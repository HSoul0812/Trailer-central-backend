<?php

namespace App\Services\CRM\Interactions;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\User\SalesPerson;
use App\Models\Integration\Auth\AccessToken;
use App\Models\User\User;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Interactions\InteractionServiceInterface;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Services\CRM\Interactions\NtlmEmailServiceInterface;
use App\Services\Integration\AuthServiceInterface;
use App\Services\Integration\Common\DTOs\AttachmentFile;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Microsoft\OfficeServiceInterface;
use Illuminate\Http\UploadedFile;
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
     * @var App\Services\Integration\AuthServiceInterface
     */
    protected $auth;

    /**
     * @var App\Services\Integration\Google\GoogleServiceInterface
     */
    protected $google;

    /**
     * @var App\Services\Integration\Google\GmailServiceInterface
     */
    protected $gmail;

    /**
     * @var App\Services\Integration\Microsoft\OfficeServiceInterface
     */
    protected $office;

    /**
     * @var App\Services\CRM\Interactions\NtlmEmailServiceInterface
     */
    protected $ntlm;

    /**
     * @var App\Services\CRM\Interactions\InteractionEmailServiceInterface
     */
    protected $interactionEmail;

    /**
     * @var App\Repositories\CRM\Interactions\InteractionsRepositoryInterface
     */
    protected $interactions;

    /**
     * @var App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface
     */
    protected $emailHistory;

    /**
     * @var App\Repositories\Integration\Auth\TokenRepositoryInterface
     */
    protected $tokens;

    /**
     * @var App\Repositories\CRM\Leads\StatusRepositoryInterface
     */
    protected $leadStatus;


    /**
     * InteractionsRepository constructor.
     * 
     * @param AuthServiceInterface $auth
     * @param GoogleServiceInterface $google
     * @param GmailServiceInterface $gmail
     * @param OfficeServiceInterface $office
     * @param NtlmEmailServiceInterface $ntlm
     * @param InteractionEmailServiceInterface $service
     * @param InteractionsRepositoryInterface $interactions
     * @param EmailHistoryRepositoryInterface $emailHistory
     * @param TokenRepositoryInterface $tokens
     * @param StatusRepositoryInterface $leadStatus
     */
    public function __construct(
        AuthServiceInterface $auth,
        GoogleServiceInterface $google,
        GmailServiceInterface $gmail,
        OfficeServiceInterface $office,
        NtlmEmailServiceInterface $ntlm,
        InteractionEmailServiceInterface $service,
        InteractionsRepositoryInterface $interactions,
        EmailHistoryRepositoryInterface $emailHistory,
        TokenRepositoryInterface $tokens,
        StatusRepositoryInterface $leadStatus
    ) {
        // Initialize Services
        $this->auth = $auth;
        $this->google = $google;
        $this->gmail = $gmail;
        $this->office = $office;
        $this->ntlm = $ntlm;
        $this->interactionEmail = $service;

        // Initialize Repositories
        $this->interactions = $interactions;
        $this->emailHistory = $emailHistory;
        $this->tokens = $tokens;
        $this->leadStatus = $leadStatus;
    }

    /**
     * Send Email to Lead
     * 
     * @param int $leadId
     * @param array $params
     * @param array $attachments
     * @return Interaction
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function email(int $leadId, array $params, array $attachments = []): Interaction {
        // Get User
        $user = User::find($params['dealer_id']);
        $lead = Lead::findOrFail($leadId);
        $salesPerson = null;
        $interactionEmail = null;
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
        } elseif($smtpConfig->isAuthTypeOffice()) {
            // Send Office Email
            $finalEmail = $this->office->send($smtpConfig, $parsedEmail);
        } elseif($smtpConfig->isAuthTypeNtlm()) {
            $finalEmail = $this->ntlm->send($user->dealer_id, $smtpConfig, $parsedEmail);
        } else {
            $finalEmail = $this->interactionEmail->send($user->dealer_id, $smtpConfig, $parsedEmail);
            $interactionEmail = true;
        }

        // Save Lead Status
        $this->leadStatus->createOrUpdate([
            'lead_id' => $lead->identifier,
            'status' => Lead::STATUS_MEDIUM,
            'next_contact_date' => Carbon::now()->addDay()->toDateTimeString()
        ]);

        // Save Email
        return $this->saveEmail($leadId, $user->newDealerUser->user_id, $finalEmail, $salesPerson, $interactionEmail);
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

            // Set Access Token on SMTP Config
            if($smtpConfig->isAuthConfigOauth()) {
                $smtpConfig->setAccessToken($this->refreshToken($smtpConfig->accessToken));
                $smtpConfig->calcAuthConfig();
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
     * @param null|bool $interactionEmail
     * @return Interaction
     */
    private function saveEmail(int $leadId, int $userId, ParsedEmail $parsedEmail, ?SalesPerson $salesPerson = null, ?bool $interactionEmail = null): Interaction {
        // Initialize Transaction
        DB::transaction(function() use (&$parsedEmail, $leadId, $userId, $salesPerson, $interactionEmail) {
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
            $emailHistory = $this->emailHistory->createOrUpdate($parsedEmail->getParams());

            // Create Interaction Email
            if ($interactionEmail) {
                $this->interactions->createInteractionEmail([
                    'interaction_id' => $interaction->interaction_id,
                    'message_id'     => $emailHistory->message_id
                ]);
            }

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
    private function refreshToken(AccessToken $accessToken): AccessToken {
        // Validate Token
        $validate = $this->auth->validate($accessToken);
        if($validate->accessToken) {
            $accessToken = $validate->accessToken;
        }

        // Return Access Token
        return $accessToken;
    }
}
