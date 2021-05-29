<?php

namespace App\Services\CRM\Interactions;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\User;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\Integration\Common\DTOs\AttachmentFile;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\CRM\Interactions\InteractionServiceInterface;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Services\CRM\Interactions\NtlmEmailServiceInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use Illuminate\Http\UploadedFile;
use App\Services\Integration\Google\GmailServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Class InteractionEmailService
 * 
 * @package App\Services\CRM\Interactions
 */
class InteractionService implements InteractionServiceInterface
{
    /**
     * InteractionsRepository constructor.
     * 
     * @param EmailHistoryRepositoryInterface
     */
    public function __construct(
        GoogleServiceInterface $google,
        GmailServiceInterface $gmail,
        NtlmEmailServiceInterface $ntlm,
        InteractionEmailServiceInterface $service,
        InteractionsRepositoryInterface $interactions,
        EmailHistoryRepositoryInterface $emailHistory,
        TokenRepositoryInterface $tokens
    ) {
        $this->google = $google;
        $this->gmail = $gmail;
        $this->ntlm = $ntlm;
        $this->interactionEmail = $service;
        $this->interactions = $interactions;
        $this->emailHistory = $emailHistory;
        $this->tokens = $tokens;
    }

    /**
     * Send Email to Lead
     * 
     * @param int $leadId
     * @param array $params
     * @param array $attachments
     * @return Interaction || error
     */
    public function email($leadId, $params, $attachments = array()) {
        // Get User
        $user = User::find($params['dealer_id']);
        $salesPerson = null;
        if(isset($params['sales_person_id'])) {
            $salesPerson = SalesPerson::find($params['sales_person_id']);
        }

        // Merge Attachments if Necessary
        if(isset($params['attachments'])) {
            $attachments = array_merge($attachments, $params['attachments']);
        } else {
            $params['attachments'] = $attachments;
        }
        
        foreach($params['attachments'] as $key => $attachment) {
            if (!is_a($attachment, UploadedFile::class)) {
                unset($params['attachments'][$key]);
            }
        }

        // Get SMTP Config
        $smtpConfig = $this->getSmtpConfig();

        // Create Parsed Email
        $parsedEmail = $this->getParsedEmail($smtpConfig, $leadId, $params);

        // Get Draft if Exists
        $emailHistory = $this->emailHistory->findEmailDraft($smtpConfig->getUsername(), $leadId);
        if(!empty($emailHistory->email_id)) {
            $parsedEmail->setEmailHistoryId($emailHistory->email_id);
            $parsedEmail->setMessageId($emailHistory->message_id);
        }

        // Send Email
        if($smtpConfig->isAuthTypeGmail()) {
            $finalEmail = $this->gmail->send($smtpConfig, $parsedEmail);
        } elseif($smtpConfig->isAuthTypeNtlm()) {
            $finalEmail = $this->ntlm->send($user->dealer_id, $smtpConfig, $parsedEmail);
        } else {
            $finalEmail = $this->interactionEmail->send($user->dealer_id, $smtpConfig, $parsedEmail);
        }

        // Save Email
        return $this->saveEmail($leadId, $user->newDealerUser->user_id, $finalEmail, $salesPerson);
    }


    /**
     * Get Parsed Email From Params
     * 
     * @param SmtpConfig $smtpConfig
     * @param int $leadId
     * @param array $params
     * @return ParsedEmail
     */
    private function getParsedEmail(SmtpConfig $smtpConfig, int $leadId, array $params): ParsedEmail {
        // Initialize Parsed Email
        $parsedEmail = new ParsedEmail();

        // Set From Details
        $parsedEmail->setFromEmail($smtpConfig->getUsername());
        $parsedEmail->setFromName($smtpConfig->getFromName() ?? $smtpConfig->getUsername());

        // Set Lead Details
        $lead = Lead::findOrFail($leadId);
        $parsedEmail->setLeadId($lead->identifier);
        $parsedEmail->setToEmail(trim($lead->email_address));
        $parsedEmail->setToName($lead->full_name);

        // Set Email Details
        $parsedEmail->setSubject($params['subject']);
        $parsedEmail->setBody($params['body']);

        // Append Attachments
        foreach($params['attachments'] as $attachment) {
            $parsedEmail->addAttachment(AttachmentFile::getFromUploadedFile($attachment));
        }

        // Append Existing Attachments
        if(!empty($params['files'])) {
            foreach($params['files'] as $file) {
                $parsedEmail->addExistingAttachment(AttachmentFile::getFromRemoteFile($file));
            }
        }

        // Return Filled Out Parsed Email
        return $parsedEmail;
    }

    /**
     * Get SMTP Config From Auth
     * 
     * @return SmtpConfig
     */
    private function getSmtpConfig(): SmtpConfig {
        // Get User
        $user = Auth::user();

        // Check if Sales Person Exists
        if(!empty($user->sales_person)) {
            // Get SMTP Config
            $smtpConfig = SmtpConfig::fillFromSalesPerson($user->sales_person);

            // Get Sales Person Auth
            $accessToken = $this->tokens->getRelation([
                'token_type' => 'google',
                'relation_type' => 'sales_person',
                'relation_id' => $user->sales_person->id
            ]);

            // Set Access Token on SMTP Config
            if(!empty($accessToken->id)) {
                $smtpConfig->setAuthType(SmtpConfig::AUTH_GMAIL);
                $smtpConfig->setAccessToken($this->refreshToken($accessToken));
            }

            // Return SMTP Config
            return $smtpConfig;
        }

        // Get SMTP Config From Dealer
        return new SmtpConfig([
            'username' => $user->email,
            'name' => $user->name ?? ''
        ]);
    }

    /**
     * Save Email From Send Email
     * 
     * @param int $leadId
     * @param int $userId
     * @param ParsedEmail $parsedEmail
     * @param null|SalesPerson $salesPerson
     * @return Interaction
     */
    private function saveEmail(int $leadId, int $userId, ParsedEmail $parsedEmail, ?SalesPerson $salesPerson = null): Interaction {
        // Initialize Transaction
        DB::transaction(function() use (&$parsedEmail, $leadId, $userId, $salesPerson) {
            // Create or Update
            $interaction = $this->interactions->createOrUpdate([
                'id'                => $parsedEmail->getInteractionId(),
                'lead_id'           => $leadId,
                'user_id'           => $userId,
                'sales_person_id'   => !empty($salesPerson) ? $salesPerson->id : NULL,
                'interaction_type'  => 'EMAIL',
                'interaction_notes' => 'E-Mail Sent: ' . $parsedEmail->getSubject(),
                'interaction_time'  => Carbon::now()->setTimezone('UTC')->toDateTimeString(),
                'from_email'        => $parsedEmail->getFromEmail(),
                'sent_by'           => !empty($salesPerson) ? $salesPerson->email : NULL
            ]);

            // Set Interaction ID/Date
            $parsedEmail->setInteractionId($interaction->interaction_id);
            $parsedEmail->setDateNow();

            // Create or Update Email
            $this->emailHistory->createOrUpdate($parsedEmail->getParams());
        });

        // Return Interaction
        return $this->interactions->get([
            'id' => $parsedEmail->getInteractionId()
        ]);
    }

    /**
     * Check If Token is Expired, Refresh if it Is
     * 
     * @param AccessToken $accessToken
     * @return AccessToken
     */
    private function refreshToken($accessToken) {
        // Validate Token
        $validate = $this->google->validate($accessToken);
        if(!empty($validate['new_token'])) {
            $accessToken = $this->tokens->refresh($accessToken->id, $validate['new_token']);
        }

        // Return Access Token
        return $accessToken;
    }
}
