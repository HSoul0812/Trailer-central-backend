<?php

namespace App\Services\CRM\Leads;

use App\Jobs\CRM\Leads\AutoAssignJob;
use App\Jobs\Email\AutoResponderJob;
use App\Models\CRM\Leads\Lead;
use App\Repositories\Website\Tracking\TrackingRepositoryInterface;
use App\Repositories\Website\Tracking\TrackingUnitRepositoryInterface;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Services\CRM\Leads\InquiryServiceInterface;
use App\Services\CRM\Email\InquiryEmailServiceInterface;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class LeadService
 * 
 * @package App\Services\CRM\Leads
 */
class InquiryService implements InquiryServiceInterface
{
    use DispatchesJobs;

    /**
     * @var App\Repositories\Website\Tracking\TrackingRepositoryInterface
     */
    protected $tracking;

    /**
     * @var App\Repositories\Website\Tracking\TrackingUnitRepositoryInterface
     */
    protected $trackingUnits;

    /**
     * @var App\Services\CRM\Leads\LeadServiceInterface
     */
    protected $leads;

    /**
     * @var App\Services\CRM\Leads\InquiryEmailServiceInterface
     */
    protected $inquiry;

    /**
     * LeadService constructor.
     */
    public function __construct(
        TrackingRepositoryInterface $tracking,
        TrackingUnitRepositoryInterface $trackingUnit,
        LeadServiceInterface $leads,
        InquiryEmailServiceInterface $inquiryEmail
    ) {
        // Initialize Services
        $this->leads = $leads;
        $this->inquiryEmail = $inquiryEmail;

        // Initialize Repositories
        $this->tracking = $tracking;
        $this->trackingUnit = $trackingUnit;
    }


    /**
     * Send Inquiry
     * 
     * @param array $params
     * @return Lead
     */
    public function send(array $params): Lead {
        // Fix Units of Interest
        $params['inventory'] = isset($params['inventory']) ? $params['inventory'] : [];
        if(!empty($params['item_id']) && !in_array($params['inquiry_type'], InquiryLead::NON_INVENTORY_TYPES)) {
            $params['inventory'][] = $params['item_id'];
        }

        // Get Inquiry Lead
        $inquiry = $this->inquiryEmail->fill($params);

        // Send Inquiry Email
        $this->inquiryEmail->send($inquiry);

        // Create Lead
        $lead = $this->leads->create($params);

        // Lead Exists?!
        if(!empty($lead->identifier)) {
            // Queue Up Inquiry Jobs
            $this->queueInquiryJobs($lead, $inquiry);
        }

        // Create Lead
        return $lead;
    }


    /**
     * Queue Up Inquiry Jobs
     * 
     * @param Lead $lead
     * @param InquiryLead $inquiry
     */
    private function queueInquiryJobs(Lead $lead, InquiryLead $inquiry) {
        // Create Auto Assign Job
        if(empty($lead->leadStatus->sales_person_id)) {
            AutoAssignJob::dispatchNow($lead);
        }

        // Dispatch Auto Responder Job
        $job = new AutoResponderJob($lead);
        $this->dispatch($job->onQueue('mails'));

        // Tracking Cookie Exists?
        if(isset($inquiry->cookieSessionId)) {
            // Set Tracking to Current Lead
            $this->tracking->updateTrackLead($inquiry->cookieSessionId, $lead->identifier);

            // Mark Track Unit as Inquired for Unit
            if(!empty($inquiry->itemId)) {
                $this->trackingUnit->markUnitInquired($inquiry->cookieSessionId, $inquiry->itemId, $inquiry->getUnitType());
            }
        }
    }
}