<?php

namespace App\Services\CRM\Email;

use App\Exceptions\CRM\Email\Builder\SendBuilderEmailsFailedException;
use App\Exceptions\CRM\Email\Builder\SendBlastEmailsFailedException;
use App\Exceptions\CRM\Email\Builder\SendCampaignEmailsFailedException;
use App\Exceptions\CRM\Email\Builder\SendTemplateEmailFailedException;
use App\Exceptions\CRM\Email\Builder\FromEmailMissingSmtpConfigException;
use App\Exceptions\CRM\Email\Builder\InvalidEmailTemplateHtmlException;
use App\Jobs\CRM\Interactions\EmailBuilderJob;
use App\Mail\CRM\Interactions\EmailBuilderEmail;
use App\Mail\CRM\Interactions\InvalidTemplateEmail;
use App\Models\CRM\Email\Blast;
use App\Models\CRM\Interactions\EmailHistory;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\Integration\Auth\AccessToken;
use App\Models\User\NewUser;
use App\Repositories\CRM\Email\BlastRepositoryInterface;
use App\Repositories\CRM\Email\BounceRepositoryInterface;
use App\Repositories\CRM\Email\CampaignRepositoryInterface;
use App\Repositories\CRM\Email\TemplateRepositoryInterface;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use App\Services\CRM\Interactions\DTOs\BuilderStats;
use App\Services\CRM\Interactions\NtlmEmailServiceInterface;
use App\Services\Integration\AuthServiceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Microsoft\OfficeServiceInterface;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;
use App\Transformers\CRM\Email\BuilderEmailTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Carbon\Carbon;

/**
 * Class EmailBuilderService
 *
 * @package App\Services\CRM\Email
 */
class EmailBuilderService implements EmailBuilderServiceInterface
{
    use DispatchesJobs, CustomerHelper, MailHelper;

    /**
     * @var App\Repositories\CRM\Email\BlastRepositoryInterface
     */
    protected $blasts;

    /**
     * @var App\Repositories\CRM\Leads\StatusRepository
     */
    protected $leadStatus;

    /**
     * @var App\Repositories\CRM\Email\CampaignRepositoryInterface
     */
    protected $campaigns;

    /**
     * @var App\Repositories\CRM\Email\TemplateRepositoryInterface
     */
    protected $templates;

    /**
     * @var App\Repositories\CRM\Email\BounceRepositoryInterface
     */
    protected $bounces;

    /**
     * @var App\Repositories\CRM\Leads\LeadRepositoryInterface
     */
    protected $leads;

    /**
     * @var App\Repositories\CRM\User\SalesPersonRepositoryInterface
     */
    protected $salespeople;

    /**
     * @var App\Repositories\CRM\Interactions\InteractionsRepositoryInterface
     */
    protected $interactions;

    /**
     * @var App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface
     */
    protected $emailhistory;

    /**
     * @var App\Repositories\Integration\Auth\TokenRepositoryInterface
     */
    protected $tokens;

    /**
     * @var App\Repositories\User\UserRepositoryInterface
     */
    protected $users;

    /**
     * @var App\Services\CRM\Interactions\NtlmEmailServiceInterface
     */
    protected $ntlm;

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
     * @var Illuminate\Support\Facades\Log
     */
    protected $log;


    /**
     * @param BlastRepositoryInterface $blasts
     * @param CampaignRepositoryInterface $campaigns
     * @param TemplateRepositoryInterface $templates
     * @param BounceRepositoryInterface $bounces
     * @param LeadRepositoryInterface $leads
     * @param SalesPersonRepositoryInterface $salespeople
     * @param InteractionsRepositoryInterface $interactions
     * @param EmailHistoryRepositoryInterface $emailhistory
     * @param TokenRepositoryInterface $tokens
     * @param UserRepositoryInterface $users
     * @param NtlmEmailServiceInterface $ntlm
     * @param AuthServiceInterface $auth
     * @param GoogleServiceInterface $google
     * @param GmailServiceInterface $gmail
     * @param OfficeServiceInterface $office
     * @param Manager $fractal
     */
    public function __construct(
        BlastRepositoryInterface $blasts,
        StatusRepositoryInterface $leadStatus,
        CampaignRepositoryInterface $campaigns,
        TemplateRepositoryInterface $templates,
        BounceRepositoryInterface $bounces,
        LeadRepositoryInterface $leads,
        SalesPersonRepositoryInterface $salespeople,
        InteractionsRepositoryInterface $interactions,
        EmailHistoryRepositoryInterface $emailhistory,
        TokenRepositoryInterface $tokens,
        UserRepositoryInterface $users,
        NtlmEmailServiceInterface $ntlm,
        AuthServiceInterface $auth,
        GoogleServiceInterface $google,
        GmailServiceInterface $gmail,
        OfficeServiceInterface $office,
        Manager $fractal
    ) {
        $this->blasts = $blasts;
        $this->leadStatus = $leadStatus;
        $this->campaigns = $campaigns;
        $this->templates = $templates;
        $this->bounces = $bounces;
        $this->leads = $leads;
        $this->salespeople = $salespeople;
        $this->interactions = $interactions;
        $this->emailhistory = $emailhistory;
        $this->tokens = $tokens;
        $this->users = $users;

        $this->ntlm = $ntlm;
        $this->auth = $auth;
        $this->google = $google;
        $this->gmail = $gmail;
        $this->office = $office;

        // Set Fractal
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new NoDataArraySerializer());

        // Initialize Logger
        $this->log = Log::channel('emailbuilder');
    }

    /**
     * Send Lead Emails for Blast
     *
     * @param Blast $blast Model of Blast to Send Emails For
     * @throws FromEmailMissingSmtpConfigException
     * @throws SendBlastEmailsFailedException
     * @return array response
     */
    public function sendBlast(Blast $blast): array {
        // Get Sales Person
        if(!empty($blast->from_email_address)) {
            $salesPerson = $this->salespeople->getBySmtpEmail($blast->user_id, $blast->from_email_address);
            if(empty($salesPerson->id)) {
                $this->log->error("From Email Address " . $blast->from_email_address .
                                    " does not exist for Email Blast #" . $blast->email_blasts_id);
                throw new FromEmailMissingSmtpConfigException;
            }
        }

        // Create Email Builder Email!
        $this->log->info("Sending Email Blast #" . $blast->email_blasts_id);
        $builder = new BuilderEmail([
            'id' => $blast->email_blasts_id,
            'type' => BuilderEmail::TYPE_BLAST,
            'name' => $blast->campaign_name,
            'subject' => $blast->campaign_subject,
            'template' => $blast->template->html,
            'template_id' => $blast->template->template_id,
            'dealer_id' => $blast->newDealerUser->id,
            'user_id' => $blast->user_id,
            'sales_person_id' => $salesPerson->id ?? null,
            'from_email' => $blast->from_email_address ?: $this->getDefaultFromEmail()
        ]);

        // Validate Template
        $this->validateTemplate($builder);

        // Send Emails and Return Response
        try {
            // Dispatch Send EmailBuilder Job
            $job = new EmailBuilderJob($builder, $blast->lead_ids);
            $this->dispatch($job->onQueue('emailbuilder'));
            $this->log->info("Dispatched Email Builder Job for Blast #" . $blast->email_blasts_id);

            // Mark Blast as Delivered
            $this->blasts->update(['id' => $builder->id, 'delivered' => 1]);

            // Return Array of Queued Leads
            return $this->response($builder, $blast->lead_ids);
        } catch(\Exception $ex) {
            throw new SendBlastEmailsFailedException($ex);
        }
    }

    /**
     * Send Lead Emails for Campaign
     *
     * @param int $id ID of Campaign to Send Emails For
     * @param string Comma-Delimited String of Lead ID's to Send Emails For Blast
     * @throws FromEmailMissingSmtpConfigException
     * @throws SendCampaignEmailsFailedException
     * @return array response
     */
    public function sendCampaign(int $id, string $leads): array {
        // Get Campaign Details
        $campaign = $this->campaigns->get(['id' => $id]);

        // Get Sales Person
        if(!empty($campaign->from_email_address)) {
            $salesPerson = $this->salespeople->getBySmtpEmail($campaign->user_id, $campaign->from_email_address);
            if(empty($salesPerson->id)) {
                throw new FromEmailMissingSmtpConfigException;
            }
        }

        // Create Email Builder Email!
        $builder = new BuilderEmail([
            'id' => $campaign->drip_campaigns_id,
            'type' => BuilderEmail::TYPE_CAMPAIGN,
            'subject' => $campaign->campaign_subject,
            'template' => $campaign->template->html,
            'template_id' => $campaign->template->template_id,
            'dealer_id' => $campaign->newDealerUser->id,
            'user_id' => $campaign->user_id,
            'sales_person_id' => $salesPerson->id ?? null,
            'from_email' => $campaign->from_email_address ?: $this->getDefaultFromEmail()
        ]);

        // Send Emails and Return Response
        try {
            // Get Lead ID's
            $leadIds = new Collection(explode(",", $leads));

            // Dispatch Send EmailBuilder Job
            $job = new EmailBuilderJob($builder, $leadIds);
            $this->dispatch($job->onQueue('emailbuilder'));
            $this->log->info("Dispatched Email Builder Job for Campaign #" . $campaign->drip_campaigns_id);

            // Return Array of Queued Leads
            return $this->response($builder, $leadIds);
        } catch(\Exception $ex) {
            throw new SendCampaignEmailsFailedException($ex);
        }
    }

    /**
     * Send Email for Template
     *
     * @param int $id ID of Template to Send Email For
     * @param string $subject Subject of Email to Send
     * @param string $toEmail Email Address to Send To
     * @param int $salesPersonId ID of Sales Person to Send From
     * @param string $fromEmail Email to Send From
     * @throws FromEmailMissingSmtpConfigException
     * @throws SendTemplateEmailFailedException
     * @return array response
     */
    public function sendTemplate(
        int $id,
        string $subject,
        string $toEmail,
        int $salesPersonId = 0,
        string $fromEmail = ''
    ): array {
        // Get Campaign Details
        $template = $this->templates->get(['id' => $id]);

        // Get Sales Person
        if(!empty($fromEmail) || !empty($salesPersonId)) {
            if(!empty($fromEmail)) {
                $salesPerson = $this->salespeople->getBySmtpEmail($template->user_id, $fromEmail);
            }
            if(empty($salesPerson->id)) {
                $salesPerson = $this->salespeople->get(['sales_person_id' => $salesPersonId]);
            }
            if(empty($salesPerson->id)) {
                throw new FromEmailMissingSmtpConfigException;
            }
            $fromEmail = $salesPerson->smtp_email;
        }

        // Create Email Builder Email!
        $builder = new BuilderEmail([
            'id' => $id,
            'type' => BuilderEmail::TYPE_TEMPLATE,
            'subject' => $subject,
            'template' => $template->html,
            'template_id' => $id,
            'dealer_id' => $template->newDealerUser->id,
            'user_id' => $template->user_id,
            'sales_person_id' => $salesPerson->id ?? null,
            'from_email' => $fromEmail ?: $this->getDefaultFromEmail(),
        ]);

        // Send Email and Return Response
        try {
            return $this->sendManual($builder, $toEmail);
        } catch(\Exception $ex) {
            throw new SendTemplateEmailFailedException($ex);
        }
    }

    /**
     * Send Test Email for Template
     *
     * @param int $dealerId ID of Dealer to Handle Test for
     * @param int $userId ID of CRM User to Handle Test for
     * @param string $subject Subject of Email to Send
     * @param string $html HTML Content of Email to Send
     * @param string $toEmail Email Address to Send To
     * @param int $salesPersonId ID of Sales Person to Send From
     * @param string $fromEmail Email to Send From
     * @throws FromEmailMissingSmtpConfigException
     * @throws SendTemplateEmailFailedException
     * @return array response
     */
    public function testTemplate(
        int $dealerId,
        int $userId,
        string $subject,
        string $html,
        string $toEmail
    ): array {
        // Create Email Builder Email!
        $builder = new BuilderEmail([
            'id' => 1,
            'type' => BuilderEmail::TYPE_TEMPLATE,
            'subject' => $subject,
            'template' => $html,
            'template_id' => 1,
            'dealer_id' => $dealerId,
            'user_id' => $userId,
            'sales_person_id' => null,
            'from_email' => $this->getDefaultFromEmail(),
        ]);

        // Send Email and Return Response
        try {
            return $this->sendManual($builder, $toEmail);
        } catch(\Exception $ex) {
            throw new SendTemplateEmailFailedException($ex);
        }
    }


    /**
     * Send Emails for Builder Config
     *
     * @param BuilderEmail $builder
     * @param Collection<int> $leads
     * @throws SendBuilderEmailsFailedException
     * @return BuilderStats
     */
    public function sendEmails(BuilderEmail $builder, Collection $leads): BuilderStats
    {
        // Initialize Counts
        $stats = new BuilderStats();

        // Loop Leads
        foreach($leads as $leadId) {
            // Add Lead Config to Builder Email
            try {
                $lead = $this->leads->get(['id' => $leadId]);
            } catch(\Exception $e) {
                $this->log->error("Exception returned trying to get lead #" . $leadId . ": " . $e->getMessage());
                $stats->updateStats(BuilderStats::STATUS_ERROR);
                continue;
            }
            $builder->setLeadConfig($lead);

            // Log to Database
            $email = $this->saveToDb($builder);
            $builder->setEmailId($email->email_id);

            // No Email Address!
            if(empty($lead->email_address)) {
                $this->log->info('The Lead With ID #' . $builder->leadId . ' has no email address, ' .
                                    'we cannot send it in Email ' . $builder->type . ' #' . $builder->id . '!');
                $stats->updateStats(BuilderStats::STATUS_BOUNCED);
                $this->markBounced($builder, 'invalid');
                continue;
            }

            // Send Lead Email
            $status = $this->sendLeadEmail($builder, $leadId);
            if ($status === BuilderStats::STATUS_SUCCESS) {
                $this->updateLead($lead);
            }
            $stats->updateStats($status);
        }

        // Return Sent Emails Collection
        return $stats;
    }

    /**
     * Update Lead Status
     *
     * @param Lead $lead
     * @return LeadStatus
     */
    private function updateLead(Lead $lead): LeadStatus
    {
        // If there was no status, or it was uncontacted, set to medium, otherwise, don't change.
        if (empty($lead->leadStatus) || $lead->leadStatus->status === Lead::STATUS_UNCONTACTED) {
            $status = Lead::STATUS_MEDIUM;
        } else {
            $status = $lead->leadStatus->status;
        }

        return $this->leadStatus->createOrUpdate([
            'lead_id' => $lead->identifier,
            'status' => $status,
            'next_contact_date' => Carbon::now()->addDay()->toDateTimeString()
        ]);
    }

    /**
     * Save Email Information to Database
     *
     * @param BuilderEmail $builder
     * @return EmailHistory
     */
    public function saveToDb(BuilderEmail $builder): EmailHistory {
        // Create Interaction
        if(!empty($builder->leadId)) {
            $interaction = $this->interactions->create([
                'lead_id'           => $builder->leadId,
                'user_id'           => $builder->userId,
                'sales_person_id'   => $builder->salesPersonId,
                'interaction_type'  => 'EMAIL',
                'interaction_notes' => 'E-Mail Sent: ' . $builder->subject,
                'interaction_time'  => Carbon::now()->setTimezone('UTC')->toDateTimeString(),
                'from_email'        => $builder->fromEmail,
                'sent_by'           => $builder->fromEmail
            ]);
        }

        // Create Email History Entry
        $email = $this->emailhistory->create($builder->getEmailHistoryParams($interaction->interaction_id ?? 0));
        sleep(1);
        return $email;
    }

    /**
     * Send Email Via SMTP|Gmail|NTLM
     *
     * @param BuilderEmail $builder
     * @return ParsedEmail
     */
    public function sendEmail(BuilderEmail $builder): ParsedEmail {
        // Get Parsed Email
        $parsedEmail = $builder->getParsedEmail($builder->emailId);

        // Get Smtp Config
        $smtpConfig = null;
        if($builder->salesPersonId) {
            $salesPerson = $this->salespeople->get(['sales_person_id' => $builder->salesPersonId]);
            $smtpConfig = !empty($salesPerson->id) ? SmtpConfig::fillFromSalesPerson($salesPerson) : null;
        }

        // Refresh Access Token if Exists
        if(!empty($smtpConfig) && $smtpConfig->isAuthConfigOauth()) {
            $accessToken = $this->refreshAccessToken($smtpConfig->accessToken);
            $smtpConfig->setAccessToken($accessToken);
        }

        // Send Gmail Email
        if(!empty($smtpConfig) && $smtpConfig->isAuthTypeGmail()) {
            $finalEmail = $this->gmail->send($smtpConfig, $parsedEmail);
        } elseif(!empty($smtpConfig) && $smtpConfig->isAuthTypeOffice()) {
            // Send Office Email
            $finalEmail = $this->office->send($smtpConfig, $parsedEmail);
        } elseif(!empty($smtpConfig) && $smtpConfig->isAuthTypeNtlm()) {
            // Send NTLM Email
            $finalEmail = $this->ntlm->send($builder->dealerId, $smtpConfig, $parsedEmail);
        } elseif($smtpConfig) {
            // Send Custom Email
            $this->sendCustomEmail($smtpConfig, $builder->getToEmail(), new EmailBuilderEmail($parsedEmail));
        } else {
            // Send SES Email
            $user = $this->users->get(['dealer_id' => $builder->dealerId]);
            $this->sendCustomSesEmail($user, $builder->getToEmail(), new EmailBuilderEmail($parsedEmail, $builder));
            $parsedEmail->setMessageId('');
        }

        // Return Final Email
        $this->log->info('Sent Email ' . $builder->type . ' #' . $builder->id .
                         ' via ' . $builder->getAuthConfig() .
                         ' to: ' . $parsedEmail->getTo());
        return $finalEmail ?? $parsedEmail;
    }

    /**
     * Mark Email as Sent
     *
     * @param BuilderEmail $builder
     * @return boolean true if marked as sent (for campaign/blast) | false if nothing marked sent
     */
    public function markSent(BuilderEmail $builder): bool {
        // Handle Based on Type
        $this->log->info('Marking ' . $builder->type . ' #' . $builder->id .
                            ' as sent for the Lead #' . $builder->leadId);
        try {
            switch($builder->type) {
                case "campaign":
                    $sent = $this->campaigns->sent($builder->id, $builder->leadId);
                break;
                case "blast":
                    $sent = $this->blasts->sent($builder->id, $builder->leadId);
                break;
            }
        } catch(\Exception $ex) {
            $this->log->error('The email ' . $builder->type . ' #' . $builder->id .
                                ' for Lead #' . $builder->leadId . ' was already marked sent');
        }

        // Return False if Nothing Saved
        return !empty($sent->lead_id);
    }

    /**
     * Add Message ID to Sent
     *
     * @param BuilderEmail $builder
     * @param ParsedEmail $parsedEmail
     * @return boolean true if marked as sent (for campaign/blast) | false if nothing marked sent
     */
    public function markSentMessageId(BuilderEmail $builder, ParsedEmail $parsedEmail): bool {
        // Handle Based on Type
        $this->log->info('Updating ' . $builder->type . ' #' . $builder->id . ' sent for the Lead #' .
                            $builder->leadId . ' with Message-ID: ' . $parsedEmail->messageId);
        switch($builder->type) {
            case "campaign":
                $sent = $this->campaigns->updateSent($builder->id, $builder->leadId, $parsedEmail->messageId, $parsedEmail->emailHistoryId);
            break;
            case "blast":
                $sent = $this->blasts->updateSent($builder->id, $builder->leadId, $parsedEmail->messageId, $parsedEmail->emailHistoryId);
            break;
        }

        // Return False if Nothing Saved
        return !empty($sent->lead_id);
    }

    /**
     * Mark Email as Sent
     *
     * @param BuilderEmail $builder
     * @param ParsedEmail $finalEmail
     * @return boolean true if marked as sent (for campaign/blast) | false if nothing marked sent
     */
    public function markEmailSent(ParsedEmail $finalEmail): bool {
        // Initialize Update Params
        $updateParams = [
            'id' => $finalEmail->emailHistoryId,
            'message_id' => $finalEmail->messageId,
            'body' => $finalEmail->body,
            'date_sent' => 1
        ];

        // Set Date Sent
        $this->log->info('Marking email #' . $finalEmail->emailHistoryId . ' as sent ' .
                            ' with Message-ID: ' . $finalEmail->messageId);
        $final = $this->emailhistory->update($updateParams);

        // Return False if Nothing Saved
        return !empty($final->email_id);
    }

    /**
     * Replace Message ID in Email History ID and Sent
     *
     * @param string $type
     * @param int $id
     * @param int $lead
     * @param int $emailHistoryId
     * @param string $messageId
     * @return boolean true if successfully found and replaced
     */
    public function replaceMessageId(string $type, int $id, int $lead, int $emailHistoryId, string $messageId): bool {
        // Get Email History Entry
        try {
            $this->log->info('Attempting to Replace Message ID ' . $messageId . ' on ' . $type . ' #' . $id);
            switch($type) {
                case "campaign":
                    $sent = $this->campaigns->updateSent($id, $lead, $messageId, $emailHistoryId);
                break;
                case "blast":
                    $sent = $this->blasts->updateSent($id, $lead, $messageId, $emailHistoryId);
                break;
            }

            // Replace in Email History
            $this->emailhistory->update(['id' => $emailHistoryId, 'message_id' => $messageId, 'date_sent' => 1]);
        } catch (\Exception $ex) {
            $this->log->error('Failed to Replace Message ID ' . $messageId . ' on ' . $type .
                                ' #' . $id . ', error returned: ' . $ex->getMessage());
        }

        // Return False if Nothing Updated
        if(!empty($sent)) {
            $this->log->info('Replaced Message ID ' . $messageId . ' on ' . $type . ' #' . $id);
        } else {
            $this->log->error('Could Not Replace Message ID ' . $messageId . ' on Non-Existent ' . $type . ' #' . $id);
        }
        return !empty($sent);
    }


    /**
     * Send Lead Email and Return Status from BuilderEmail
     *
     * @param BuilderEmail $builder
     * @return string
     */
    private function sendLeadEmail(BuilderEmail $builder): string
    {
        // Try/Send Email!
        try {
            // Already Exists?
            if(($builder->type === BuilderEmail::TYPE_BLAST && $this->blasts->wasSent($builder->id, $builder->toEmail)) ||
               ($builder->type === BuilderEmail::TYPE_CAMPAIGN && $this->campaigns->wasSent($builder->id, $builder->toEmail))) {
                $this->log->info('Already Sent Email ' . $builder->type . ' #' . $builder->id . ' to Email Address: ' . $builder->toEmail);
                $this->markBounced($builder);
                return BuilderStats::STATUS_DUPLICATE;
            }

            // Already Marked Sent?
            $this->markSent($builder);

            // Email Bounced!
            if($type = $this->bounces->wasBounced($builder->toEmail)) {
                $this->log->info('The Email Address ' . $builder->toEmail . ' was already marked as ' .
                                    $type . ', so we cannot send it in Email ' . $builder->type . ' #' . $builder->id . '!');
                $this->markBounced($builder, $type);
                return BuilderStats::STATUS_BOUNCED;
            }

            // Send Email Via SMTP, Gmail, or NTLM
            $finalEmail = $this->sendEmail($builder);

            $this->markSentMessageId($builder, $finalEmail);

            // Mark Email as Sent Only if Not SES!
            if($finalEmail->messageId) {
                $this->markEmailSent($finalEmail);
            }

            // Send Notice
            $this->log->info('Sent Email ' . $builder->type . ' #' . $builder->id . ' to Email Address: ' . $builder->toEmail);
            return BuilderStats::STATUS_SUCCESS;
        } catch(\Exception $ex) {
            $this->log->error($ex->getMessage());
            return BuilderStats::STATUS_ERROR;
        }
    }

    /**
     * Handle Text Template Validation
     *
     * @param BuilderEmail $builder
     * @throws InvalidEmailTemplateHtmlException
     * @return bool
     */
    private function validateTemplate(BuilderEmail $builder): bool
    {
        // Template is Valid?!
        if($builder->template) {
            return true;
        }

        // Send Invalid Template Email
        $dealer = $this->users->get(['dealer_id' => $builder->dealerId]);
        $credential = NewUser::getDealerCredential($dealer->newDealerUser->user_id);
        $launchUrl = Lead::getLeadCrmUrl($builder->leadId, $credential);
        try {
            Mail::to($dealer->email)->send(new InvalidTemplateEmail($builder, $launchUrl));
        } catch(\Exception $e) {
            $this->log->error('Exception trying to send invalid template email: ' . $e->getMessage());
        }

        // Fix Blast to Remove Template ID
        if($builder->type === BuilderEmail::TYPE_BLAST) {
            // Remove Invalid Template ID
            $this->blasts->update(['id' => $builder->id, 'email_template_id' => 0]);
        }
        // Fix Campaign to Remove Template ID
        elseif($builder->type === BuilderEmail::TYPE_CAMPAIGN) {
            $this->campaigns->update(['id' => $builder->id, 'email_template_id' => 0, 'is_enabled' => 0]);
        }

        // Send Email to Dealer!
        throw new InvalidEmailTemplateHtmlException;
    }

    /**
     * Send Email Manually for Builder Config
     *
     * @param BuilderEmail $builder
     * @param string $toEmail
     * @throws SendBuilderEmailsFailedException
     * @return array response
     */
    private function sendManual(BuilderEmail $builder, string $toEmail): array {
        // Try/Send Email!
        try {
            // Add To Email to Builder Email
            $builder->setToEmail($toEmail);

            // Log to Database
            $email = $this->saveToDb($builder);
            $builder->setEmailId($email->email_id);

            // Send Email Directly
            $finalEmail = $this->sendEmail($builder);

            // Mark Email As Sent
            $this->markSent($builder);
            $this->markEmailSent($finalEmail);

            // Send Notice
            $this->log->info('Sent Email ' . $builder->type . ' #' .
                    $builder->id . ' to Email: ' . $toEmail);

            // Return Response Array
            return $this->response($builder, new Collection([$toEmail]));
        } catch(\Exception $ex) {
            $this->log->error($ex->getMessage());
            throw new SendBuilderEmailsFailedException;
        }
    }

    /**
     * Mark Email as Bounced
     *
     * @param BuilderEmail $builder
     * @param null|string $type
     * @return void
     */
    private function markBounced(BuilderEmail $builder, ?string $type = null): void
    {
        // Ignore if: Lead ID Already Exists in Sent AND We're Marking as Skipped
        if($type === null &&
          (($builder->type === BuilderEmail::TYPE_BLAST && $this->blasts->wasLeadSent($builder->id, $builder->leadId)) ||
          ($builder->type === BuilderEmail::TYPE_CAMPAIGN && $this->campaigns->wasLeadSent($builder->id, $builder->leadId)))) {
            $this->log->info('Sent status already applied for ' . $builder->type . ' #' . $builder->id . ' lead #' . $builder->leadId);
        } else {
            // Get Parsed Email
            $parsedEmail = $builder->getParsedEmail($builder->emailId);
            $this->log->info('Marking ' . $builder->type . ' #' . $builder->id . ' as ' .
                                ($type ?? 'skipped') . ' for the Message-ID ' . $parsedEmail->messageId);

            // Create Or Update Bounced Entry in DB
            $this->emailhistory->update([
                'id' => $builder->emailId,
                'message_id' => $parsedEmail->messageId,
                'body' => $parsedEmail->body,
                'was_skipped' => 1,
                'date_bounced' => ($type === 'bounce') ? 1 : 0,
                'date_complained' => ($type === 'complaint') ? 1 : 0,
                'date_unsubscribed' => ($type === 'unsubscribe') ? 1 : 0,
                'invalid_email' => ($type === 'invalid') ? 1 : 0
            ]);

            // Mark Sent With Message ID
            $this->markSentMessageId($builder, $parsedEmail);
        }
    }

    /**
     * Return Send Emails Response
     *
     * @param BuilderEmail $builder
     * @param Collection<int> $leads
     * @return array response
     */
    private function response(BuilderEmail $builder, Collection $leads): array {
        // Convert Builder Email to Fractal
        $data = new Item($builder, new BuilderEmailTransformer(), 'data');
        $response = $this->fractal->createData($data)->toArray();

        // Convert Builder Email to Fractal
        $response['leads'] = $leads->toArray();

        // Return Response
        return $response;
    }

    /**
     * Refresh Access Token
     *
     * @param AccessToken $accessToken
     * @return AccessToken
     */
    private function refreshAccessToken(AccessToken $accessToken): AccessToken {
        // Refresh Token
        $validate = $this->auth->validate($accessToken);
        if($validate->accessToken) {
            $accessToken = $validate->accessToken;
        }

        // Return New Token
        return $accessToken;
    }
}
