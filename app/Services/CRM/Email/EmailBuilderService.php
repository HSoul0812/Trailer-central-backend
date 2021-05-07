<?php

namespace App\Services\CRM\Email;

use App\Exceptions\CRM\Email\Builder\SendBuilderEmailsFailedException;
use App\Exceptions\CRM\Email\Builder\SendBlastEmailsFailedException;
use App\Exceptions\CRM\Email\Builder\SendCampaignEmailsFailedException;
use App\Exceptions\CRM\Email\Builder\SendTemplateEmailFailedException;
use App\Exceptions\CRM\Email\Builder\FromEmailMissingSmtpConfigException;
use App\Jobs\CRM\Interactions\SendEmailBuilderJob;
use App\Repositories\CRM\Email\BlastRepositoryInterface;
use App\Repositories\CRM\Email\CampaignRepositoryInterface;
use App\Repositories\CRM\Email\TemplateRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use App\Traits\CustomerHelper;
use App\Transformers\CRM\Email\BuilderEmailTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class EmailBuilderService
 * 
 * @package App\Services\CRM\Email
 */
class EmailBuilderService implements EmailBuilderServiceInterface
{
    use DispatchesJobs, CustomerHelper;

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
     * @var App\Repositories\CRM\Leads\LeadsRepositoryInterface
     */
    protected $leads;

    /**
     * @var App\Repositories\CRM\User\SalesPersonRepositoryInterface
     */
    protected $salespeople;

    /**
     * @var Illuminate\Support\Facades\Log
     */
    protected $log;

    /**
     * @param SalesPersonRepositoryInterface $salespeople
     */
    public function __construct(
        BlastRepositoryInterface $blasts,
        CampaignRepositoryInterface $campaigns,
        TemplateRepositoryInterface $templates,
        LeadRepositoryInterface $leads,
        SalesPersonRepositoryInterface $salespeople,
        Manager $fractal
    ) {
        $this->blasts = $blasts;
        $this->campaigns = $campaigns;
        $this->templates = $templates;
        $this->leads = $leads;
        $this->salespeople = $salespeople;

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
     * @throws SendBlastEmailsFailedException
     * @return array response
     */
    public function sendBlast(int $id, array $leads): array {
        // Get Blast Details
        $blast = $this->blasts->get(['id' => $id]);

        // Get Sales Person
        $salesPerson = $this->salespeople->getBySmtpEmail($blast->user_id, $blast->from_email_address);
        if(empty($salesPerson->id)) {
            throw new FromEmailMissingSmtpConfigException;
        }

        // Create Email Builder Email!
        $builder = new BuilderEmail([
            'id' => $blast->email_blasts_id,
            'type' => BuilderEmail::TYPE_BLAST,
            'subject' => $blast->campaign_subject,
            'template' => $blast->template->html,
            'template_id' => $blast->template->template_id,
            'user_id' => $blast->user_id,
            'sales_person_id' => $salesPerson->id,
            'from_email' => $blast->from_email_address,
            'smtp_config' => SmtpConfig::fillFromSalesPerson($salesPerson)
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
     * @throws SendCampaignEmailsFailedException
     * @return array response
     */
    public function sendCampaign(int $id, array $leads): array {
        // Get Campaign Details
        $campaign = $this->campaigns->get(['id' => $id]);

        // Get Sales Person
        $salesPerson = $this->salespeople->getBySmtpEmail($campaign->user_id, $campaign->from_email_address);
        if(empty($salesPerson->id)) {
            throw new FromEmailMissingSmtpConfigException;
        }

        // Create Email Builder Email!
        $builder = new BuilderEmail([
            'id' => $campaign->drip_campaigns_id,
            'type' => BuilderEmail::TYPE_CAMPAIGN,
            'subject' => $campaign->campaign_subject,
            'template' => $campaign->template->html,
            'template_id' => $campaign->template->template_id,
            'user_id' => $campaign->user_id,
            'sales_person_id' => $salesPerson->id,
            'from_email' => $campaign->from_email_address,
            'smtp_config' => SmtpConfig::fillFromSalesPerson($salesPerson)
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
        $campaign = $this->campaigns->get(['id' => $id]);

        // Get Sales Person
        $salesPerson = $this->salespeople->getBySmtpEmail($campaign->user_id, $campaign->from_email_address);
        if(empty($salesPerson->id)) {
            throw new FromEmailMissingSmtpConfigException;
        }

        // Create Email Builder Email!
        $builder = new BuilderEmail([
            'id' => $campaign->drip_campaigns_id,
            'type' => BuilderEmail::TYPE_CAMPAIGN,
            'subject' => $campaign->campaign_subject,
            'template' => $campaign->template->html,
            'template_id' => $campaign->template->template_id,
            'user_id' => $campaign->user_id,
            'sales_person_id' => $salesPerson->id,
            'from_email' => $campaign->from_email_address,
            'smtp_config' => SmtpConfig::fillFromSalesPerson($salesPerson)
        ]);

        // Send Emails and Return Response
        try {
            return $this->sendEmails($builder, $leads);
        } catch(\Exception $ex) {
            throw new SendCampaignEmailsFailedException($ex);
        }
    }


    /**
     * Send Emails for Builder Config
     * 
     * @param BuilderEmail $builder
     * @param array $leads
     * @return Collection<int> Collection of Lead ID's That Started Sending
     */
    private function sendEmails(BuilderEmail $builder, array $leads) {
        // Initialize Sent Emails Collection
        $sentEmails = new Collection();
        $errorEmails = new Collection();

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

                // Add Lead Config to Builder Email
                $builder->setLeadConfig($lead);

                // Dispatch Send EmailBuilder Job
                $job = new SendEmailBuilderJob($builder);
                $this->dispatch($job->onQueue('mails'));

                // Send Notice
                $sentEmails->push($leadId);
                $this->log->info('Sent Email ' . $builder->type . ' #' . $builder->id . ' to Lead with ID: ' . $leadId);
            } catch(\Exception $ex) {
                $this->log->error($ex->getMessage(), $ex->getTrace());
                $errorEmails->push($leadId);
            }
        }

        // Errors Occurred and No Emails Sent?
        if($sentEmails->count() < 1 && $errorEmails->count() > 0) {
            throw new SendBuilderEmailsFailedException;
        }

        // Return Sent Emails Collection
        return $this->response($builder, $sentEmails, $errorEmails);
    }

    /**
     * Return Send Emails Response
     * 
     * @param BuilderEmail $builder
     * @param Collection<int> $sent Lead ID's Successfully Queued to Send
     * @param Collection<int> $errors Lead ID's That Failed to Queue
     * @return Response
     */
    private function response(BuilderEmail $builder, Collection $sent, Collection $errors): array {
        // Handle Logging
        $this->log->info('Queued ' . $sent->count() . ' Email ' . $builder->type .
                '(s) for Dealer #' . $builder->userId);
        if($errors->count() > 0) {
            $this->log->info('Errors Occurring Trying to Queue ' . $errors->count() . ' Email ' . $builder->type .
                    '(s) for Dealer #' . $builder->userId);
        }

        // Convert Builder Email to Fractal
        $data = new Item($builder, new BuilderEmailTransformer(), 'data');
        $response = $this->fractal->createData($data)->toArray();

        // Set Succesfull Emails and Errors
        $response['sent'] = $sent->toArray();
        $response['errors'] = $errors->toArray();

        // Return Response
        return $response;
    }
}
