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
use App\Repositories\CRM\Email\CampaignRepositoryInterface;
use App\Repositories\CRM\Email\TemplateRepositoryInterface;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use App\Services\CRM\Interactions\NtlmEmailServiceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;
use App\Transformers\CRM\Email\BuilderEmailTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
     * @param LeadRepositoryInterface $leads
     * @param SalesPersonRepositoryInterface $salespeople
     * @param EmailHistoryRepositoryInterface $emailhistory
     * @param TokenRepositoryInterface $tokens
     * @param GoogleServiceInterface $google
     * @param GmailServiceInterface $gmail
     * @param Manager $fractal
     */
    public function __construct(
        BlastRepositoryInterface $blasts,
        CampaignRepositoryInterface $campaigns,
        TemplateRepositoryInterface $templates,
        LeadRepositoryInterface $leads,
        SalesPersonRepositoryInterface $salespeople,
        InteractionsRepositoryInterface $interactions,
        EmailHistoryRepositoryInterface $emailhistory,
        TokenRepositoryInterface $tokens,
        NtlmEmailServiceInterface $ntlm,
        GoogleServiceInterface $google,
        GmailServiceInterface $gmail,
        Manager $fractal
    ) {
        $this->blasts = $blasts;
        $this->campaigns = $campaigns;
        $this->templates = $templates;
        $this->leads = $leads;
        $this->salespeople = $salespeople;
        $this->interactions = $interactions;
        $this->emailhistory = $emailhistory;
        $this->tokens = $tokens;

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
            'from_email' => $blast->from_email_address ?: $this->getDefaultFromEmail(),
            'smtp_config' => !empty($salesPerson->id) ? SmtpConfig::fillFromSalesPerson($salesPerson) : null
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
            'from_email' => $campaign->from_email_address ?: $this->getDefaultFromEmail(),
            'smtp_config' => !empty($salesPerson->id) ? SmtpConfig::fillFromSalesPerson($salesPerson) : null
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
            'smtp_config' => !empty($salesPerson->id) ? SmtpConfig::fillFromSalesPerson($salesPerson) : null
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

        // Get SMTP Config
        if(!empty($config->isAuthTypeGmail())) {
            // Get Access Token
            $accessToken = $this->refreshAccessToken($config->smtpConfig->accessToken);
            $config->smtpConfig->setAccessToken($accessToken);

            // Send Gmail Email
            $finalEmail = $this->gmail->send($config->smtpConfig, $parsedEmail);
        }
        // Get NTLM Config
        elseif(!empty($config->isAuthTypeNtlm())) {
            // Send NTLM Email
            $finalEmail = $this->ntlm->send($config->dealerId, $config->smtpConfig, $parsedEmail);
        }
        // Get SMTP Config
        else {
            $this->setSmtpConfig($config->smtpConfig);

            // Send Email
            Mail::to($this->getCleanTo($config->getToEmail()))
                ->send(new EmailBuilderEmail($parsedEmail));
            $finalEmail = $parsedEmail;
        }

        // Return Final Email
        $this->log->info('Sent Email ' . $config->type . ' #' . $config->id .
                         ' via ' . $config->getAuthConfig() .
                         ' to: ' . $finalEmail->getTo());
        return $finalEmail;
    }

    /**
     * Mark Email as Sent
     * 
     * @param BuilderEmail $config
     * @return boolean true if marked as sent (for campaign/blast) | false if nothing marked sent
     */
    public function markSent(BuilderEmail $config): bool {
        // Handle Based on Type
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
     * Mark Email as Sent
     * 
     * @param BuilderEmail $config
     * @param null|ParsedEmail $finalEmail
     * @return boolean true if marked as sent (for campaign/blast) | false if nothing marked sent
     */
    public function markEmailSent(ParsedEmail $finalEmail = null): bool {
        // Set Date Sent
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
        // Initialize Sent Emails Collection
        $sentEmails = [];
        $sentLeads = new Collection();
        $errorLeads = new Collection();

        // Loop Leads
        foreach($leads as $leadId) {
            // Already Exists?
            if(($builder->type === BuilderEmail::TYPE_BLAST && $this->blasts->wasSent($builder->id, $leadId)) ||
               ($builder->type === BuilderEmail::TYPE_CAMPAIGN && $this->campaigns->wasSent($builder->id, $leadId))) {
                continue;
            }

            // Try/Send Email!
            try {
                // Get Lead
                $lead = $this->leads->get(['id' => $leadId]);
                if(in_array($lead->email_address, $sentEmails)) {
                    continue;
                }
                $sentEmails[] = $lead->email_address;

                // Add Lead Config to Builder Email
                $builder->setLeadConfig($lead);

                // Log to Database
                $email = $this->saveToDb($builder);
                $builder->setEmailId($email->email_id);
                $this->markSent($builder);

                // Dispatch Send EmailBuilder Job
                $job = new SendEmailBuilderJob($builder);
                $this->dispatch($job->onQueue('emailbuilder'));

                // Send Notice
                $sentLeads->push($leadId);
                $this->log->info('Sent Email ' . $builder->type . ' #' .
                        $builder->id . ' to Lead with ID: ' . $leadId);
            } catch(\Exception $ex) {
                $this->log->error($ex->getMessage(), $ex->getTrace());
                $errorLeads->push($leadId);
            }
        }

        // Errors Occurred and No Emails Sent?
        if($sentLeads->count() < 1 && $errorLeads->count() > 0) {
            throw new SendBuilderEmailsFailedException;
        }

        // Return Sent Emails Collection
        return $this->response($builder, $sentLeads, $errorLeads);
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
            return $this->response($builder, new Collection([$builder->id]));
        } catch(\Exception $ex) {
            $this->log->error($ex->getMessage(), $ex->getTrace());
            throw new SendBuilderEmailsFailedException;
        }
    }

    /**
     * Return Send Emails Response
     * 
     * @param BuilderEmail $builder
     * @param null|Collection<int> $sent Lead ID's Successfully Queued to Send
     * @param null|Collection<int> $errors Lead ID's That Failed to Queue
     * @return array response
     */
    private function response(BuilderEmail $builder, ?Collection $sent = null, ?Collection $errors = null): array {
        // Handle Logging
        if($sent !== null) {
            $this->log->info('Queued ' . $sent->count() . ' Email ' .
                    $builder->type . '(s) for Dealer #' . $builder->userId);
        }
        if($errors !== null && $errors->count() > 0) {
            $this->log->info('Errors Occurring Trying to Queue ' .
                    $errors->count() . ' Email ' . $builder->type .
                    '(s) for Dealer #' . $builder->userId);
        }

        // Convert Builder Email to Fractal
        $data = new Item($builder, new BuilderEmailTransformer(), 'data');
        $response = $this->fractal->createData($data)->toArray();

        // Set Succesfull Emails and Errors
        if($sent !== null) {
            $response['sent'] = $sent->toArray();
        }
        if($errors !== null) {
            $response['errors'] = $errors->toArray();
        }

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
