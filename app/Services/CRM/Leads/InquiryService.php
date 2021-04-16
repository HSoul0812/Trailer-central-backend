<?php

namespace App\Services\CRM\Leads;

use App\Jobs\CRM\Leads\AutoAssignJob;
use App\Jobs\Email\AutoResponderJob;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadType;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\Website\Tracking\TrackingRepositoryInterface;
use App\Repositories\Website\Tracking\TrackingUnitRepositoryInterface;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Services\CRM\Leads\InquiryServiceInterface;
use App\Services\CRM\Leads\Export\ADFServiceInterface;
use App\Services\CRM\Email\InquiryEmailServiceInterface;
use Illuminate\Database\Eloquent\Collection;
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
     * @var App\Repositories\CRM\Leads\LeadRepositoryInterface
     */
    protected $leadRepo;

    /**
     * @var App\Repositories\Website\Tracking\TrackingRepositoryInterface
     */
    protected $tracking;

    /**
     * @var App\Repositories\Website\Tracking\TrackingUnitRepositoryInterface
     */
    protected $trackingUnit;

    /**
     * @var App\Services\CRM\Leads\LeadServiceInterface
     */
    protected $leads;

    /**
     * @var App\Services\CRM\Leads\InquiryEmailServiceInterface
     */
    protected $inquiryEmail;

    /**
     * @var App\Services\CRM\Leads\Export\ADFServiceInterface
     */
    protected $adf;

    /**
     * LeadService constructor.
     */
    public function __construct(
        LeadRepositoryInterface $leadRepo,
        TrackingRepositoryInterface $tracking,
        TrackingUnitRepositoryInterface $trackingUnit,
        LeadServiceInterface $leads,
        InquiryEmailServiceInterface $inquiryEmail,
        ADFServiceInterface $adf
    ) {
        // Initialize Services
        $this->leads = $leads;
        $this->inquiryEmail = $inquiryEmail;
        $this->adf = $adf;

        // Initialize Repositories
        $this->leadRepo = $leadRepo;
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
        $lead = $this->mergeOrCreate($params);

        // Lead Exists?!
        if(!empty($lead->identifier)) {
            // Queue Up Inquiry Jobs
            $this->queueInquiryJobs($lead, $inquiry);
        }

        // Create Lead
        return $lead;
    }

    /**
     * Merge or Create Lead
     * 
     * @param array $params
     * @return Lead
     */
    public function mergeOrCreate(array $params): Lead {
        // Lead Type is NOT Financing?
        if(in_array(LeadType::TYPE_FINANCING, $params['lead_types'])) {
            // Get Matches
            $leads = $this->leadRepo->findAllMatches($params);

            // Choose Matching Lead
            $lead = $this->chooseMatch($leads, $params);

            // Merge Lead!
            if(!empty($lead->identifier)) {
                return $this->leads->merge($lead, $params);
            }
        }

        // Create!
        return $this->leads->create($params);
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

        // Export ADF if Possible
        $this->adf->export($inquiry, $lead);

        // Tracking Cookie Exists?
        if($inquiry->cookieSessionId) {
            // Set Tracking to Current Lead
            $this->tracking->updateTrackLead($inquiry->cookieSessionId, $lead->identifier);

            // Mark Track Unit as Inquired for Unit
            if($inquiry->itemId) {
                $this->trackingUnit->markUnitInquired($inquiry->cookieSessionId, $inquiry->itemId, $inquiry->getUnitType());
            }
        }
    }


    /**
     * Choose Matching Lead
     * 
     * @param Collection<Lead> $matches
     * @param array $params
     * @return null || Lead
     */
    private function chooseMatch(Collection $matches, array $params): ?Lead {
        // Sort Leads Into Standard or With Status
        $status = new Collection();
        $chosen = null;
        foreach($matches as $lead) {
            if(!empty($lead->leadStatus)) {
                $status->push($lead);
            }
        }

        // Create Inquiry Lead
        $inquiry = new InquiryLead($params);

        // Find By Status!
        if(!empty($status) && count($status) > 0) {
            $chosen = $this->filterMatch($status, $inquiry);
        }

        // Still Not Chosen? Find Any!
        if(empty($chosen)) {
            $chosen = $this->filterMatch($matches, $inquiry);
        }

        // Return $result
        return $chosen;
    }

    /**
     * Filter Matching Lead
     * 
     * @param Collection<Lead> $leads
     * @param FilteredLead $inquiry
     * @return null || Lead
     */
    private function filterMatch(Collection $leads, InquiryLead $inquiry): ?Lead {
        // Loop Status
        $chosen = null;
        $matches = new Collection();
        foreach($leads as $lead) {
            // Find All Matches Between Both
            $matched = $inquiry->findMatches($lead);

            // Matched All 3
            if($matched > InquiryLead::MERGE_MATCH_COUNT) {
                $chosen = $lead;
                break;
            }
            // Matched At Least 2
            elseif($matched >= InquiryLead::MERGE_MATCH_COUNT) {
                $matches->push($lead);
            }
        }

        // Get First Match
        if(empty($chosen) && $matches->count() > 0) {
            $chosen = $matches->first();
        }

        // Return Chosen Lead
        return $chosen;
    }
}