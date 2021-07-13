<?php

namespace App\Services\CRM\Email;

use App\Exceptions\CRM\Email\Builder\SendBuilderEmailsFailedException;
use App\Exceptions\CRM\Email\Builder\SendBlastEmailsFailedException;
use App\Exceptions\CRM\Email\Builder\SendCampaignEmailsFailedException;
use App\Exceptions\CRM\Email\Builder\SendTemplateEmailFailedException;
use App\Exceptions\CRM\Email\Builder\FromEmailMissingSmtpConfigException;
use App\Jobs\CRM\Interactions\SendEmailBuilderJob;
use App\Mail\CRM\Interactions\EmailBuilderEmail;
use App\Models\CRM\Interactions\EmailHistory;
use App\Models\Integration\Auth\AccessToken;
use App\Repositories\CRM\Email\BlastRepositoryInterface;
use App\Repositories\CRM\Email\BounceRepositoryInterface;
use App\Repositories\CRM\Email\CampaignRepositoryInterface;
use App\Repositories\CRM\Email\TemplateRepositoryInterface;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use App\Services\CRM\Interactions\DTOs\BuilderStats;
use App\Services\CRM\Interactions\NtlmEmailServiceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;
use App\Transformers\CRM\Email\BuilderEmailTransformer;
use App\Transformers\CRM\Email\BuilderStatsTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
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
     * @param EmailHistoryRepositoryInterface $emailhistory
     * @param TokenRepositoryInterface $tokens
     * @param UserRepositoryInterface $users
     * @param GoogleServiceInterface $google
     * @param GmailServiceInterface $gmail
     * @param Manager $fractal
     */
    public function __construct(
        BlastRepositoryInterface $blasts,
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
        GoogleServiceInterface $google,
        GmailServiceInterface $gmail,
        Manager $fractal
    ) {
        $this->blasts = $blasts;
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
        $this->google = $google;
        $this->gmail = $gmail;

        // Set Fractal
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new NoDataArraySerializer());

        // Initialize Logger
        $this->log = Log::channel('emailbuilder');
    }

    /**
     * Send Lead Emails for Blast
     * 
     * @param int $id ID of Blast to Send Emails For
     * @param array<int> ID's of Leads to Send Emails For Blast
     * @throws FromEmailMissingSmtpConfigException
     * @throws SendBlastEmailsFailedException
     * @return array response
     */
    public function sendBlast(int $id, array $leads): array {
        // Get Blast Details
        $blast = $this->blasts->get(['id' => $id]);

        // Get Sales Person
        if(!empty($blast->from_email_address)) {
            $salesPerson = $this->salespeople->getBySmtpEmail($blast->user_id, $blast->from_email_address);
            if(empty($salesPerson->id)) {
                throw new FromEmailMissingSmtpConfigException;
            }
        }

        // Create Email Builder Email!
        $builder = new BuilderEmail([
            'id' => $blast->email_blasts_id,
            'type' => BuilderEmail::TYPE_BLAST,
            'subject' => $blast->campaign_subject,
            'template' => $blast->template->html,
            'template_id' => $blast->template->template_id,
            'dealer_id' => $blast->newDealerUser->id,
            'user_id' => $blast->user_id,
            'sales_person_id' => $salesPerson->id ?? 0,
            'from_email' => $blast->from_email_address ?: $this->getDefaultFromEmail()
        ]);

        // Send Emails and Return Response
        try {
            return $this->sendEmails($builder, $leads);
        } catch(\Exception $ex) {
            throw new SendBlastEmailsFailedException($ex);
        }
    }

    /**
     * Send Lead Emails for Campaign
     * 
     * @param int $id ID of Campaign to Send Emails For
     * @param array<int> ID's of Leads to Send Emails For Campaign
     * @throws FromEmailMissingSmtpConfigException
     * @throws SendCampaignEmailsFailedException
     * @return array response
     */
    public function sendCampaign(int $id, array $leads): array {
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
            'sales_person_id' => $salesPerson->id ?? 0,
            'from_email' => $campaign->from_email_address ?: $this->getDefaultFromEmail()
        ]);

        // Send Emails and Return Response
        try {
            return $this->sendEmails($builder, $leads);
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
            'sales_person_id' => $salesPerson->id ?? 0,
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
     * Save Email Information to Database
     * 
     * @param BuilderEmail $config
     * @return EmailHistory
     */
    public function saveToDb(BuilderEmail $config): EmailHistory {
        // Create Interaction
        if(!empty($config->leadId)) {
            $interaction = $this->interactions->create([
                'lead_id'           => $config->leadId,
                'user_id'           => $config->userId,
                'sales_person_id'   => $config->salesPersonId,
                'interaction_type'  => 'EMAIL',
                'interaction_notes' => 'E-Mail Sent: ' . $config->subject,
                'interaction_time'  => Carbon::now()->setTimezone('UTC')->toDateTimeString(),
                'from_email'        => $config->fromEmail,
                'sent_by'           => $config->fromEmail
            ]);
        }

        // Create Email History Entry
        return $this->emailhistory->create($config->getEmailHistoryParams($interaction->interaction_id ?? 0));
    }

    /**
     * Send Email Via SMTP|Gmail|NTLM
     * 
     * @param BuilderEmail $config
     * @return ParsedEmail
     */
    public function sendEmail(BuilderEmail $config): ParsedEmail {
        // Get Parsed Email
        $parsedEmail = $config->getParsedEmail($config->emailId);

        // Get Smtp Config
        $salesPerson = $this->salespeople->get(['sales_person_id' => $config->salesPersonId]);
        $smtpConfig = !empty($salesPerson->id) ? SmtpConfig::fillFromSalesPerson($salesPerson) : null;

        // Send Gmail Email
        if(!empty($smtpConfig) && $smtpConfig->isAuthTypeGmail()) {
            // Refresh Token
            $accessToken = $this->refreshAccessToken($smtpConfig->accessToken);
            $smtpConfig->setAccessToken($accessToken);
            $finalEmail = $this->gmail->send($smtpConfig, $parsedEmail);
        }
        // Send NTLM Email
        elseif(!empty($smtpConfig) && $smtpConfig->isAuthTypeNtlm()) {
            $finalEmail = $this->ntlm->send($config->dealerId, $smtpConfig, $parsedEmail);
        }
        // Send Custom Email
        elseif($smtpConfig) {
            $this->sendCustomEmail($smtpConfig, $config->getToEmail(), new EmailBuilderEmail($parsedEmail));
        }
        // Send Default Email
        else {
            $user = $this->users->get(['dealer_id' => $config->dealerId]);
            $this->sendDefaultEmail($user, $config->getToEmail(), new EmailBuilderEmail($parsedEmail));
        }

        // Return Final Email
        $this->log->info('Sent Email ' . $config->type . ' #' . $config->id .
                         ' via ' . $config->getAuthConfig() .
                         ' to: ' . $parsedEmail->getTo());
        return $finalEmail ?? $parsedEmail;
    }

    /**
     * Mark Email as Sent
     * 
     * @param BuilderEmail $config
     * @return boolean true if marked as sent (for campaign/blast) | false if nothing marked sent
     */
    public function markSent(BuilderEmail $config): bool {
        // Handle Based on Type
        $this->log->info('Marking ' . $config->type . ' #' . $config->id . ' as sent for ' .
                            ' the Lead With ID #' . $config->leadId);
        switch($config->type) {
            case "campaign":
                $sent = $this->campaigns->sent([
                    'drip_campaigns_id' => $config->id,
                    'lead_id' => $config->leadId
                ]);
            break;
            case "blast":
                $sent = $this->blasts->sent([
                    'email_blasts_id' => $config->id,
                    'lead_id' => $config->leadId
                ]);
            break;
        }

        // Return False if Nothing Saved
        return !empty($sent->lead_id);
    }

    /**
     * Add Message ID to Sent
     * 
     * @param BuilderEmail $config
     * @param ParsedEmail $parsedEmail
     * @return boolean true if marked as sent (for campaign/blast) | false if nothing marked sent
     */
    public function markSentMessageId(BuilderEmail $config, ParsedEmail $parsedEmail): bool {
        // Handle Based on Type
        $this->log->info('Marking ' . $config->type . ' #' . $config->id . ' as sent for ' .
                            ' the Lead With ID #' . $config->leadId . ' with Message-ID: ' . $parsedEmail->messageId);
        switch($config->type) {
            case "campaign":
                $sent = $this->campaigns->updateSent($config->id, $config->leadId, $parsedEmail->messageId);
            break;
            case "blast":
                $sent = $this->blasts->updateSent($config->id, $config->leadId, $parsedEmail->messageId);
            break;
        }

        // Return False if Nothing Saved
        return !empty($sent->lead_id);
    }

    /**
     * Mark Email as Sent
     * 
     * @param BuilderEmail $config
     * @param ParsedEmail $finalEmail
     * @return boolean true if marked as sent (for campaign/blast) | false if nothing marked sent
     */
    public function markEmailSent(ParsedEmail $finalEmail): bool {
        // Set Date Sent
        $this->log->info('Marking email #' . $finalEmail->emailHistoryId>id . ' as sent ' .
                            ' with Message-ID: ' . $finalEmail->messageId);
        $email = $this->emailhistory->update([
            'id' => $finalEmail->emailHistoryId,
            'message_id' => $finalEmail->messageId,
            'body' => $finalEmail->body,
            'date_sent' => 1
        ]);

        // Return False if Nothing Saved
        return !empty($email->email_id);
    }


    /**
     * Send Emails for Builder Config
     * 
     * @param BuilderEmail $builder
     * @param array $leads
     * @throws SendBuilderEmailsFailedException
     * @return array response
     */
    private function sendEmails(BuilderEmail $builder, array $leads): array {
        // Initialize Counts
        $stats = new BuilderStats();

        // Loop Leads
        foreach($leads as $leadId) {
            // Add Lead Config to Builder Email
            $lead = $this->leads->get(['id' => $leadId]);
            $builder->setLeadConfig($lead);

            // Log to Database
            $email = $this->saveToDb($builder);
            $builder->setEmailId($email->email_id);

            // Email Bounced!
            if(empty($lead->email_address)) {
                $this->log->info('The Lead With ID #' . $builder->leadId . ' has no email address, ' .
                                    'we cannot send it in Email ' . $builder->type . ' #' . $builder->id . '!');
                $stats->updateStats(BuilderStats::STATUS_BOUNCED);
                $this->markBounced($builder, 'invalid');
                continue;
            }

            // Already Exists?
            if(($builder->type === BuilderEmail::TYPE_BLAST && $this->blasts->wasSent($builder->id, $lead->email_address)) ||
               ($builder->type === BuilderEmail::TYPE_CAMPAIGN && $this->campaigns->wasSent($builder->id, $lead->email_address))) {
                $this->log->info('Already Sent Email ' . $builder->type . ' #' . $builder->id . ' to Email Address: ' . $lead->email_address);
                $stats->updateStats(BuilderStats::STATUS_DUPLICATE);
                $this->markBounced($builder);
                continue;
            }

            // Send Lead Email
            $status = $this->sendLeadEmail($builder, $leadId);
            $stats->updateStats($status);
        }

        // Errors Occurred and No Emails Sent?
        if($stats->noSent < 1 && $stats->noErrors > 0) {
            throw new SendBuilderEmailsFailedException;
        }

        // Return Sent Emails Collection
        return $this->response($builder, $stats);
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
            // Mark as Sent Before Queueing
            $this->markSent($builder);

            // Email Bounced!
            if($type = $this->bounces->wasBounced($builder->toEmail)) {
                $this->log->info('The Email Address ' . $builder->toEmail . ' was already marked as ' .
                                    $type . ', so we cannot send it in Email ' . $builder->type . ' #' . $builder->id . '!');
                $this->markBounced($builder, $type);
                return BuilderStats::STATUS_BOUNCED;
            }

            // Dispatch Send EmailBuilder Job
            $job = new SendEmailBuilderJob($builder);
            $this->dispatch($job->onQueue('emailbuilder'));

            // Send Notice
            $this->log->info('Sent Email ' . $builder->type . ' #' . $builder->id . ' to Email Address: ' . $builder->toEmail);
            return BuilderStats::STATUS_SUCCESS;
        } catch(\Exception $ex) {
            $this->log->error($ex->getMessage(), $ex->getTrace());
            return BuilderStats::STATUS_ERROR;
        }
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
            return $this->response($builder, new BuilderStats(true));
        } catch(\Exception $ex) {
            $this->log->error($ex->getMessage(), $ex->getTrace());
            throw new SendBuilderEmailsFailedException;
        }
    }

    /**
     * Mark Email as Bounced
     * 
     * @param BuilderEmail $config
     * @param null|string $type
     * @return void
     */
    private function markBounced(BuilderEmail $config, ?string $type = null): void
    {
        // Get Parsed Email
        $parsedEmail = $config->getParsedEmail($config->emailId);
        $this->log->info('Marking ' . $config->type . ' #' . $config->id . ' as ' .
                            $type . ' for the Message-ID #' . $parsedEmail->messageId);

        // Create Or Update Bounced Entry in DB
        $this->emailhistory->update([
            'id' => $config->emailId,
            'message_id' => $parsedEmail->messageId,
            'body' => $parsedEmail->body,
            'was_skipped' => 1,
            'date_bounced' => ($type === 'bounce') ? 1 : 0,
            'date_complained' => ($type === 'complaint') ? 1 : 0,
            'date_unsubscribed' => ($type === 'unsubscribe') ? 1 : 0,
            'invalid_email' => ($type === 'invalid') ? 1 : 0
        ]);

        // Mark Sent With Message ID
        $this->markSentMessageId($config, $parsedEmail);
    }

    /**
     * Return Send Emails Response
     * 
     * @param BuilderEmail $builder
     * @param BuilderStats $stats
     * @return array response
     */
    private function response(BuilderEmail $builder, BuilderStats $stats): array {
        // Handle Logging
        $this->log->info('Queued ' . $stats->noSent . ' Email ' .
                            $builder->type . '(s) for Dealer #' . $builder->userId);
        $this->log->info('Skipped ' . $stats->noSkipped . ' Email ' .
                            $builder->type . '(s) for Dealer #' . $builder->userId);
        $this->log->info('Errors Occurring Trying to Queue ' . $stats->noErrors . ' Email ' .
                            $builder->type . '(s) for Dealer #' . $builder->userId);

        // Convert Builder Email to Fractal
        $data = new Item($builder, new BuilderEmailTransformer(), 'data');
        $response = $this->fractal->createData($data)->toArray();

        // Convert Builder Email to Fractal
        $status = new Item($stats, new BuilderStatsTransformer(), 'data');
        $result = $this->fractal->createData($status)->toArray();
        $response['stats'] = $result['data'];

        // Return Response
        return $response;
    }

    /**
     * Refresh Gmail Access Token
     * 
     * @param AccessToken $accessToken
     * @return AccessToken
     */
    private function refreshAccessToken(AccessToken $accessToken): AccessToken {
        // Refresh Token
        $validate = $this->google->validate($accessToken);
        if(!empty($validate['new_token'])) {
            $accessToken = $this->tokens->refresh($accessToken->id, $validate['new_token']);
        }

        // Return New Token
        return $accessToken;
    }
}
