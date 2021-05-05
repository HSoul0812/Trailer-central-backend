<?php

namespace App\Services\CRM\Email;

use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
use App\Services\CRM\Interactions\DTOs\BuilderEmail;
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
        $builder = new BuilderEmail([
            'id' => $blast->email_blasts_id,
            'type' => 'blast',
            'subject' => $blast->campaign_subject,
            'template' => $blast->template->html,
            'template_id' => $blast->template->template_id,
            'user_id' => $params['user_id'],
            'sales_person_id' => $salesPerson->id,
            'from_email' => $blast->from_email_address,
            'smtp_config' => SmtpConfig::fillFromSalesPerson($salesPerson)
        ]);

        // Loop Leads
        $successfullySent = [];
        foreach($params['leads'] as $leadId) {
            // Try/Send Email!
            try {
                // Get Lead
                $lead = $this->leads->get(['id' => $leadId]);

                // Send Email
                $this->send($this->addLeadToBuilder($builder, $lead));

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
}
