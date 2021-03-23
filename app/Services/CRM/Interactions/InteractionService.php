<?php

namespace App\Services\CRM\Interactions;

use App\Models\CRM\Leads\Lead;
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
        // Find Lead/Sales Person
        $lead = Lead::findOrFail($leadId);
        $user = Auth::user();

        // Get SMTP Config
        $smtpConfig = $this->getSmtpConfig();

        // Create Parsed Email
        $parsedEmail = $this->getParsedEmail($smtpConfig, $lead, $params, $attachments);

        // Get Draft if Exists
        $emailHistory = $this->emailHistory->findEmailDraft($smtpConfig->getUsername(), $lead->identifier);
        if(!empty($emailHistory->id)) {
            $parsedEmail->setMessageId($emailHistory->message_id);
        }

        // Send Email
        if($smtpConfig->getAuthType() === SmtpConfig::AUTH_GMAIL) {
            $finalEmail = $this->gmail->send($smtpConfig, $parsedEmail);
        } elseif($smtpConfig->getAuthType() === SmtpConfig::AUTH_NTLM) {
            $finalEmail = $this->ntlm->send($lead->dealer_id, $smtpConfig, $parsedEmail);
        } else {
            $finalEmail = $this->interactionEmail->send($lead->dealer_id, $smtpConfig, $parsedEmail);
        }

        // Save Email
        return $this->saveEmail($leadId, $user->newDealerUser->user_id, $finalEmail);
    }


    /**
     * Get Parsed Email From Params
     * 
     * @param array $params
     * @param array $attachments
     * @return ParsedEmail
     */
    private function getParsedEmail(SmtpConfig $smtpConfig, Lead $lead, array $params, array $attachments = []): ParsedEmail {
        // Initialize Parsed Email
        $parsedEmail = new ParsedEmail();

        // Set From Details
        $parsedEmail->setFromEmail($smtpConfig->getUsername());
        $parsedEmail->setFromName();

        // Set Lead Details
        $parsedEmail->setToEmail(trim($lead->email_address));
        $parsedEmail->setToName($lead->full_name);

        // Append Attachments
        $files = new Collection();
        if(!empty($params['attachments'])) {
            foreach($params['attachments'] as $attachment) {
                $files->push(new AttachmentFile($attachment));
            }
        }
        foreach($attachments as $attachment) {
            $files->push(new AttachmentFile($attachment));
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
     * @return Interaction
     */
    private function saveEmail(int $leadId, int $userId, ParsedEmail $parsedEmail): Interaction {
        // Initialize Transaction
        DB::transaction(function() use (&$parsedEmail, $leadId, $userId) {
            // Create or Update
            $interaction = $this->interactions->createOrUpdate([
                'id'                => $interaction->getInteractionId(),
                'lead_id'           => $leadId,
                'user_id'           => $userId,
                'interaction_type'  => 'EMAIL',
                'interaction_notes' => 'E-Mail Sent: ' . $parsedEmail->getSubject(),
                'interaction_time'  => Carbon::now()->setTimezone('UTC')->toDateTimeString(),
            ]);

            // Insert Email
            $params['date_sent'] = 1;
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
