<?php

namespace App\Services\CRM\Interactions;

use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
use App\Services\CRM\Interactions\DTOs\EmailBuilderConfig;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;
use Illuminate\Support\Facades\Log;

/**
 * Class EmailBuilderService
 * 
 * @package App\Services\CRM\Email
 */
class EmailBuilderService implements EmailBuilderServiceInterface
{
    use CustomerHelper, MailHelper;

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
        SalesPersonRepositoryInterface $salespeople
    ) {
        $this->salespeople = $salespeople;

        // Initialize Logger
        $this->log = Log::channel('emailbuilder');
    }

    /**
     * Send Lead Emails for Blast
     * 
     * @param array $params
     * @throws SendBlastEmailsFailedException
     * @return bool
     */
    public function sendBlast(array $params): bool {
        // Get Blast Details
        $blast = $this->blasts->get(['id' => $params['id']]);

        // Get Sales Person
        $salesPerson = $this->salespeople->getBySmtpEmail($blast->from_email_address);

        // Create Email Builder Email!
        $builder = new EmailBuilderConfig([
            'id' => $blast->email_blasts_id,
            'type' => 'blast',
            'subject' => $blast->campaign_subject,
            'template' => $blast->template->html,
            'template_id' => $blast->template->template_id,
            'from_email' => $blast->from_email_address,
            'smtp_config' => SmtpConfig::fillFromSalesPerson($salesPerson)
        ]);

        // Loop Leads
        $successfullySent = [];
        foreach($params['leads'] as $leadId) {
            // Try/Send Email!
            try {
                // Send Email
                $this->send($this->addLeadToBuilder($builder, $leadId));

                // Send Notice
                $successfullySent[] = $leadId;
                $this->log->info('Sent Email Blast #' . $blast->id . ' to Lead with ID: ' . $leadId);
            } catch(\Exception $ex) {
                $this->log->error($ex->getMessage(), $ex->getTrace());
            }
        }

        // Returns True on Success
        $this->log->info('Sent ' . count($successfullySent) . ' Email Blasts for Dealer ' . $params['dealer_id']);
        return true;
    }


    /**
     * Add Lead Details to Builder Config
     * 
     * @param EmailBuilderConfig $config
     * @param int $leadId
     * @return EmailBuilderConfig
     */
    private function addLeadToBuilder(EmailBuilderConfig $config, int $leadId): EmailBuilderConfig
    {
        // Get Lead
        $lead = $this->leads->get(['id' => $leadId]);

        // Insert Lead Details
        $config->addLeadConfig($leadId, $lead->email_address, $lead->full_name);

        // Return Updated Config
        return $config;
    }
}
