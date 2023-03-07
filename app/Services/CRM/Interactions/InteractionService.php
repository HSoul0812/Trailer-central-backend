<?php

namespace App\Services\CRM\Interactions;

use App\Exceptions\CRM\Interactions\SaveEmailInteractionUnknownException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\User\SalesPerson;
use App\Models\Integration\Auth\AccessToken;
use App\Models\User\User;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Interactions\InteractionServiceInterface;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Services\CRM\Interactions\NtlmEmailServiceInterface;
use App\Services\CRM\User\DTOs\EmailSettings;
use App\Services\Integration\AuthServiceInterface;
use App\Services\Integration\Common\DTOs\AttachmentFile;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Microsoft\OfficeServiceInterface;
use App\Traits\MailHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

/**
 * Class InteractionEmailService
 *
 * @package App\Services\CRM\Interactions
 */
class InteractionService implements InteractionServiceInterface
{
    use MailHelper;


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
     * @var UserRepositoryInterface
     */
    protected $users;

    /**
     * @var SalesPeresonRepositoryInterface
     */
    protected $salespeople;

    /**
     * @var Log
     */
    protected $log;


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
     * @param UserRepositoryInterface $users
     * @param SalesPersonRepositoryInterface $salespeople
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
        StatusRepositoryInterface $leadStatus,
        UserRepositoryInterface $users,
        SalesPersonRepositoryInterface $salespeople
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
        $this->users = $users;
        $this->salespeople = $salespeople;

        // Initialize Log File for Interactions
        $this->log = Log::channel('interaction');
    }


    /**
     * Get Email Config Settings
     *
     * @param int $dealerId
     * @param null|int $salesPersonId
     * @return EmailSettings
     */
    public function config(int $dealerId, ?int $salesPersonId = null): EmailSettings {
        // Get User Data
        $user = $this->users->get(['dealer_id' => $dealerId]);
        if(!empty($salesPersonId)) {
            $salesPerson = $this->salespeople->get(['sales_person_id' => $salesPersonId]);
        }

        // Get Default Email + Reply-To
        if(empty($salesPerson->id)) {
            return new EmailSettings([
                'dealer_id' => $dealerId,
                'type' => 'dealer',
                'method' => 'smtp',
                'config' => EmailSettings::CONFIG_DEFAULT,
                'perms' => 'admin',
                'from_email' => config('mail.from.address'),
                'from_name' => $user->name,
                'reply_email' => $user->email,
                'reply_name' => $user->name
            ]);
        }


        // Get SMTP Config
        $smtpConfig = SmtpConfig::fillFromSalesPerson($salesPerson);

        // Set Access Token on SMTP Config
        if($smtpConfig->isAuthConfigOauth()) {
            $smtpConfig->setAccessToken($this->refreshToken($smtpConfig->accessToken));
            $smtpConfig->calcAuthConfig();
        }

        // SMTP Valid?
        $smtpValid = $salesPerson->smtp_validate->success;
        if(!$smtpValid) {
            $smtpValid = $smtpConfig->isAuthConfigOauth();
        }

        // Get Sales Person Settings
        return new EmailSettings([
            'dealer_id' => $dealerId,
            'sales_person_id' => $salesPersonId,
            'type' => 'sales_person',
            'method' => $smtpConfig->isAuthConfigOauth() ? EmailSettings::METHOD_OAUTH : EmailSettings::METHOD_DEFAULT,
            'config' => $smtpValid ? $smtpConfig->getAuthConfig() : EmailSettings::CONFIG_DEFAULT,
            'perms' => $salesPerson->perms,
            'from_email' => $smtpValid ? $smtpConfig->getUsername() : config('mail.from.address'),
            'from_name' => $smtpConfig->getFromName(),
            'reply_email' => !$smtpValid ? $smtpConfig->getUsername() : null,
            'reply_name' => !$smtpValid ? $smtpConfig->getFromName() : null
        ]);
    }

    /**
     * Send Email to Lead
     *
     * @param array $params
     * @param array $attachments
     * @return Interaction
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function email(array $params, array $attachments = []): Interaction {
        // Get User
        $user = $this->users->get(['dealer_id' => $params['dealer_id']]);
        $this->log->info('Found Dealer ID #' . $user->dealer_id . ' to Send Email From');

        // Initialize SMTP Config
        $smtpConfig = null;
        $salesPerson = null;
        $interactionEmail = null;
        if(!empty($params['sales_person_id'])) {
            $salesPerson = $this->salespeople->get(['sales_person_id' => $params['sales_person_id']]);
            $this->log->info('Found Sales Person #' . $salesPerson->id . ' To Send Email From');

            // Get SMTP Config
            $smtpConfig = SmtpConfig::fillFromSalesPerson($salesPerson);
            if($smtpConfig->isAuthConfigOauth()) {
                $smtpConfig->setAccessToken($this->refreshToken($smtpConfig->accessToken));
                $smtpConfig->calcAuthConfig();
            }
        }

        if(!isset($params['attachments'])) {
            $params['attachments'] = $attachments;
        }

        if ($params['attachments'] instanceof UploadedFile) {
            $params['attachments'] = array_fill(0, 1, $params['attachments']);
        }

        $this->log->info('Found ' . count($params['attachments']) . ' Attachments To Send Via Email');
        foreach($params['attachments'] as $key => $attachment) {
            if (!is_a($attachment, UploadedFile::class)) {
                unset($params['attachments'][$key]);
            }
        }

        // Get From Email
        if($smtpConfig !== null) {
            $fromEmail = $smtpConfig->getUsername();
        } else {
            $fromEmail = $params['from_email'] = config('mail.from.address');
            $params['from_name'] = $user->name;
        }
        $this->log->info('Got Email ' . $fromEmail . ' To Send From');

        // Create Parsed Email
        $parsedEmail = $this->getParsedEmail($smtpConfig, $params);
        $this->log->info('Configured Email With Subject ' . $parsedEmail->subject . ' To Send to Lead');

        // Get Draft if Exists
        $emailHistory = $this->emailHistory->findEmailDraft($fromEmail, $params['lead_id'], $params['quote_id']);
        if(!empty($emailHistory->email_id)) {
            $this->log->info('Found Draft #' . $emailHistory->email_id . ' of Email We Are Currently Sending');
            $parsedEmail->setEmailHistoryId($emailHistory->email_id);
            $parsedEmail->setMessageId($emailHistory->message_id);
        }

        // Send Email
        $this->log->info('Sending Email From ' . $parsedEmail->from . ' To ' . $parsedEmail->to);
        if($smtpConfig !== null && $smtpConfig->isAuthTypeGmail()) {
            $finalEmail = $this->gmail->send($smtpConfig, $parsedEmail);
        } elseif($smtpConfig !== null && $smtpConfig->isAuthTypeOffice()) {
            // Send Office Email
            $finalEmail = $this->office->send($smtpConfig, $parsedEmail);
        } elseif($smtpConfig !== null && $smtpConfig->isAuthTypeNtlm()) {
            $finalEmail = $this->ntlm->send($user->dealer_id, $smtpConfig, $parsedEmail);
        } else {
            $emailConfig = $this->config($user->dealer_id, !empty($salesPerson) ? $salesPerson->id : null);
            $finalEmail = $this->interactionEmail->send($emailConfig, $smtpConfig, $parsedEmail);
            $interactionEmail = true;
        }

        if (!empty($params['lead_id'])) {
            $lead = Lead::findOrFail($params['lead_id']);
            $this->log->info('Found Lead ID #' . $lead->identifier . ' to Send Email To');

            // If there was no status, or it was uncontacted, set to medium, otherwise, don't change.
            if (empty($lead->leadStatus) || $lead->leadStatus->status === Lead::STATUS_UNCONTACTED) {
                $status = Lead::STATUS_MEDIUM;
            } else {
                $status = $lead->leadStatus->status;
            }

            $this->leadStatus->createOrUpdate([
                'lead_id' => $lead->identifier,
                'status' => $status,
                'next_contact_date' => Carbon::now()->addDay()->toDateTimeString()
            ]);
        }

        // Save Email
        $this->log->info('Saving Email From ' . $parsedEmail->from . ' To ' . $parsedEmail->to . ' into DB');
        return $this->saveEmail($params, $user->newDealerUser->user_id, $finalEmail, $salesPerson, $interactionEmail);
    }


    /**
     * Get Parsed Email From Params
     *
     * @param null|SmtpConfig $smtpConfig
     * @param array $params
     * @return ParsedEmail
     */
    private function getParsedEmail(?SmtpConfig $smtpConfig, array $params): ParsedEmail {
        // Initialize Parsed Email
        $parsedEmail = new ParsedEmail();

        // Set From Details
        if($smtpConfig !== null) {
            $parsedEmail->setFromEmail($smtpConfig->getUsername());
            $parsedEmail->setFromName($smtpConfig->getFromName() ?? $smtpConfig->getUsername());
        } else {
            $parsedEmail->setFromEmail($params['from_email']);
            $parsedEmail->setFromName($params['from_name']);
        }

        if (!empty($params['lead_id'])) {
            // Set Lead Details
            $lead = Lead::findOrFail($params['lead_id']);
            $parsedEmail->setLeadId($lead->identifier);
            $parsedEmail->setToEmail(trim($lead->email_address));
            $parsedEmail->setToName($lead->full_name);
        }
        
        if (!empty($params['quote_id'])) {
            $parsedEmail->setQuoteId($params['quote_id']);
            $parsedEmail->setToEmail(trim($params['to']));
        }
        
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
     * Save Email From Send Email
     *
     * @param array $params
     * @param int $userId
     * @param ParsedEmail $parsedEmail
     * @param null|SalesPerson $salesPerson
     * @param null|bool $interactionEmail
     * @throws SaveEmailInteractionUnknownException
     * @return Interaction
     */
    private function saveEmail(array $params, int $userId, ParsedEmail $parsedEmail, ?SalesPerson $salesPerson = null, ?bool $interactionEmail = null): Interaction {
        // Initialize Transaction
        $interaction = null;
        DB::transaction(function() use (&$interaction, $parsedEmail, $params, $userId, $salesPerson, $interactionEmail) {
            // Create or Update
            $interaction = $this->interactions->createOrUpdate([
                'id'                => $parsedEmail->getInteractionId(),
                'lead_id'           => (!empty($params['lead_id'])) ? trim($params['lead_id']) : '',
                'quote_id'          => (int) $params['quote_id'] ?? '',
                'user_id'           => $userId,
                'sales_person_id'   => !empty($salesPerson) ? $salesPerson->id : NULL,
                'interaction_type'  => 'EMAIL',
                'interaction_notes' => 'E-Mail Sent: ' . $parsedEmail->getSubject(),
                'interaction_time'  => Carbon::now()->setTimezone('UTC')->toDateTimeString(),
                'from_email'        => $parsedEmail->getFromEmail(),
                'sent_by'           => !empty($salesPerson) ? $salesPerson->email : NULL
            ]);
            $this->log->info('Created Interaction #' . $interaction->interaction_id . ' for Sent Email');

            // Create or Update Email
            $emailHistory = $this->emailHistory->createOrUpdate($parsedEmail->getParams());
            $interaction->setRelation('emailHistory', new Collection([$emailHistory]));
            $this->log->info('Created Email #' . $emailHistory->email_id . ' for Sent Email');

            // Create Interaction Email
            if ($interactionEmail) {
                $this->interactions->createInteractionEmail([
                    'interaction_id' => $interaction->interaction_id,
                    'message_id'     => $emailHistory->message_id
                ]);
                $this->log->info('Connected Interaction #' . $interaction->interaction_id .
                                    ' to Email #' . $emailHistory->email_id . ' for Sent Email');
            }
        });

        // Return Interaction
        if(!empty($interaction)) {
            $this->log->info('Returning Interaction #' . $interaction->interaction_id . ' for Sent Email');
            return $interaction;
        }

        // Throw Exception
        $this->log->error('Unknown error occurred trying to save email ' .
                            'From ' . $parsedEmail->from . ' To ' . $parsedEmail->to);
        throw new SaveEmailInteractionUnknownException;
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

    /**
     * Get Next Contact Date details
     */
    public function getContactDate(int $leadId)
    {
        $leadStatus = $this->leadStatus->get(['lead_id' => $leadId]);
        $contactDate = $leadStatus->next_contact_date ?? null;
        $interactionNote = '';

        if ($contactDate === '0000-00-00 00:00:00' || empty($contactDate)) {
            $contactDate = date('Y-m-d H:i:s');
        }

        $interaction = $this->interactions->getInteractionByTime($leadId, $contactDate);

        if (!empty($interaction)) {
            $interactionNote = $interaction->interaction_notes;
        }

        return [
            'contact_date' => $contactDate,
            'task_details' => $interactionNote
        ];
    }
}
