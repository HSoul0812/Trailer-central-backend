<?php

namespace App\Services\CRM\Leads;

use App\Jobs\CRM\Leads\AutoAssignJob;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\Interactions\Interaction;
use App\Models\User\User;
use App\Models\Website\Config\WebsiteConfig;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Website\Tracking\TrackingRepositoryInterface;
use App\Repositories\Website\Tracking\TrackingUnitRepositoryInterface;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Services\CRM\Leads\Export\ADFServiceInterface;
use App\Services\CRM\Leads\Export\IDSServiceInterface;
use App\Services\CRM\Email\InquiryEmailServiceInterface;
use App\Services\CRM\Text\InquiryTextServiceInterface;
use App\Services\Website\WebsiteConfigServiceInterface;
use App\Utilities\Fractal\NoDataArraySerializer;
use App\Transformers\CRM\Leads\LeadTransformer;
use App\Transformers\CRM\Interactions\InteractionTransformer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

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
     * @var App\Services\CRM\Text\InquiryTextServiceInterface;
     */
    protected $inquiryText;

    /**
     * @var App\Services\CRM\Leads\Export\ADFServiceInterface
     */
    protected $adf;

    /**
     * @var App\Services\CRM\Leads\Export\IDSServiceInterface
     */
    protected $ids;

    /**
     * @var Illuminate\Support\Facades\Log
     */
    protected $log;


    /**
     * @var App\Transformers\CRM\Leads\LeadTransformer
     */
    private $leadTransformer;

    /**
     * @var App\Transformers\CRM\Interactions\InteractionTransformer
     */
    private $interactionTransformer;

    /**
     * @var Manager
     */
    private $fractal;

    /** @var WebsiteConfigServiceInterface */
    private $webConfigService;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepo;

    /**
     * LeadService constructor.
     */
    public function __construct(
        LeadRepositoryInterface $leadRepo,
        TrackingRepositoryInterface $tracking,
        TrackingUnitRepositoryInterface $trackingUnit,
        TextRepositoryInterface $texts,
        LeadServiceInterface $leads,
        InquiryEmailServiceInterface $inquiryEmail,
        InquiryTextServiceInterface $inquiryText,
        ADFServiceInterface $adf,
        IDSServiceInterface $ids,
        LeadTransformer $leadTransformer,
        InteractionTransformer $interactionTransformer,
        Manager $fractal,
        WebsiteConfigServiceInterface $webConfigService,
        UserRepositoryInterface $userRepo
    ) {
        // Initialize Services
        $this->leads = $leads;
        $this->inquiryEmail = $inquiryEmail;
        $this->inquiryText = $inquiryText;
        $this->adf = $adf;
        $this->ids = $ids;
        $this->webConfigService = $webConfigService;

        // Initialize Repositories
        $this->leadRepo = $leadRepo;
        $this->tracking = $tracking;
        $this->trackingUnit = $trackingUnit;
        $this->userRepo = $userRepo;
        $this->texts = $texts;

        // Set Up Fractal
        $this->leadTransformer = $leadTransformer;
        $this->interactionTransformer = $interactionTransformer;
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new NoDataArraySerializer());

        // Get Logger
        $this->log = Log::channel('inquiry');
    }


    /**
     * Create Inquiry
     *
     * @param array $params
     * @return array{data: Lead,
     *               merge: null|Interaction}
     */
    public function create(array $params): array
    {
        // Fix Units of Interest
        $params['inventory'] = isset($params['inventory']) ? $params['inventory'] : [];
        if (!empty($params['item_id']) && !in_array($params['inquiry_type'], InquiryLead::NON_INVENTORY_TYPES)) {
            $params['inventory'][] = $params['item_id'];
        }

        // Get Inquiry Lead
        $inquiry = $this->inquiryEmail->fill($params);
        $this->log->info('Creating ' . $inquiry->inquiryType . ' inquiry email for ' . $inquiry->getInquiryTo());

        // Create or Merge Lead
        return $this->mergeOrCreate($inquiry, $params);
    }

    /**
     * Send Inquiry
     *
     * @param array $params
     * @return array{data: Lead,
     *               merge: null|Interaction}
     */
    public function send(array $params): array
    {
        // Fix Units of Interest
        $params['inventory'] = isset($params['inventory']) ? $params['inventory'] : [];
        if (!empty($params['item_id']) && !in_array($params['inquiry_type'], InquiryLead::NON_INVENTORY_TYPES)) {
            $params['inventory'][] = $params['item_id'];
        }

        // Get Inquiry Lead
        $inquiry = $this->inquiryEmail->fill($params);

        // Send Inquiry Email
        $this->log->info('Sending ' . $inquiry->inquiryType . ' inquiry email for ' . $inquiry->getInquiryTo());
        $this->inquiryEmail->send($inquiry);

        // Merge or Create Lead
        return $this->mergeOrCreate($inquiry, $params);
    }

    /**
     * Text Inquiry
     *
     * @param array $params
     * @return array{data: Lead,
     *               merge: null|Interaction}
     * @throws \App\Exceptions\PropertyDoesNotExists
     */
    public function text(array $params): array
    {
        // Fix Units of Interest
        $params['inventory'] = isset($params['inventory']) ? $params['inventory'] : [];
        if (!empty($params['inventory_id'])) {
            $params['inventory'][] = $params['inventory_id'];
        }

        // Clean Up Inquiry Text Response
        $params = $this->inquiryText->merge($params);

        $inquiry = new InquiryLead($params);

        // Send Inquiry Text
        $sent = $this->inquiryText->send($params);

        // Merge or Create Lead
        $lead = $this->mergeOrCreate($inquiry, $params);

        // Create Text In DB
        $this->texts->create([
            'lead_id'     => $lead['data']['id'],
            'from_number' => $params['phone_number'], // customer number
            'to_number'   => $sent->to, // dealer number
            'log_message' => $params['sms_message']
        ]);

        // Return Lead Data
        return $lead;
    }

    /**
     * Merge or Create Lead
     *
     * @param InquiryLead $inquiry
     * @param array $params
     * @return array{data: Lead,
     *               merge: null|Interaction}
     */
    public function mergeOrCreate(InquiryLead $inquiry, array $params): array
    {
        // Lead Type is NOT Financing?
        $interaction = null;

        /** @var User $dealer */
        $dealer = $this->userRepo->get(['dealer_id' => (int)$inquiry->dealerId]);
        $isCrmActive = $dealer && $dealer->isCrmActive; // when the dealer does not have active the CRM, then it should not merge leads

        if ($isCrmActive && !in_array(LeadType::TYPE_FINANCING, $params['lead_types'])) {
            // Check merge is enabled for given website.
            $configData = $this->webConfigService->getConfigByWebsite($params['website_id'], WebsiteConfig::LEADS_MERGE_ENABLED);
            if (!empty($configData[WebsiteConfig::LEADS_MERGE_ENABLED]) && $configData[WebsiteConfig::LEADS_MERGE_ENABLED] === "1") {
                // Get Matches
                $leads = $this->leadRepo->findAllMatches($params);

                // Choose Matching Lead
                $lead = $this->chooseMatch($leads, $params);

                // Merge Lead!
                if (!empty($lead->identifier)) {
                    $this->log->info('Merged lead inquiry into #' . $lead->identifier);
                    $interaction = $this->leads->mergeInquiry($lead, $params);

                    // Update Existing Lead
                    $lead = $this->leads->update([
                        'id' => $lead->identifier,
                        'inventory' => array_merge($lead->inventory_ids, $params['inventory']),
                        'lead_types' => array_merge($lead->lead_types, $params['lead_types']),
                        'is_archived' => Lead::NOT_ARCHIVED,
                        'status' => Lead::STATUS_NEW_INQUIRY,
                    ]);
                }
            }
        }

        // Create Lead!
        if (empty($lead->identifier)) {
            $lead = $this->leads->create($params);
            $this->log->info('Created new lead #' . $lead->identifier);
        }

        // Lead Exists?!
        if (!empty($lead->identifier)) {
            // Queue Up Inquiry Jobs
            $this->log->info('Handling jobs on lead #' . $lead->identifier);
            $this->queueInquiryJobs($lead, $inquiry);
        }

        // Return Fractal Response
        return $this->response($lead, $interaction);
    }


    /**
     * Return Response
     *
     * @param Lead $lead
     * @param null|Interaction $interaction
     * @return array{data: Lead,
     *               merge: null|Interaction}
     */
    private function response(Lead $lead, ?Interaction $interaction = null)
    {
        // Convert Lead to Array
        $leadData = new Item($lead, $this->leadTransformer, 'data');
        $response = $this->fractal->createData($leadData)->toArray();

        // Convert Interaction to Array
        $response['merge'] = null;
        if (!empty($interaction->interaction_id)) {
            $interactionData = new Item($interaction, $this->interactionTransformer, 'data');
            $interactionResponse = $this->fractal->createData($interactionData)->toArray();
            $response['merge'] = $interactionResponse['data'];
        }

        // Return Response
        return $response;
    }


    /**
     * Queue Up Inquiry Jobs
     *
     * @param Lead $lead
     * @param InquiryLead $inquiry
     */
    private function queueInquiryJobs(Lead $lead, InquiryLead $inquiry)
    {
        // Create Auto Assign Job
        if (empty($lead->leadStatus->sales_person_id) && empty($lead->is_spam)) {
            // Dispatch Auto Assign Job
            $this->log->info('Handling auto assign on lead #' . $lead->identifier);
            $job = new AutoAssignJob($lead);
            $this->dispatch($job->onQueue('inquiry'));
        }

        // Export ADF if Possible
        if (!in_array(LeadType::TYPE_FINANCING, $inquiry->leadTypes) && empty($lead->is_spam)) {
            $this->log->info('Handling ADF export on lead #' . $lead->identifier);
            $this->adf->export($lead);

            $this->log->info('Handling IDS export on lead #' . $lead->identifier);
            $this->ids->exportInquiry($lead);
        }

        // Tracking Cookie Exists?
        if ($inquiry->cookieSessionId) {
            // Set Tracking to Current Lead
            $this->log->info('Handling lead tracking on lead #' . $lead->identifier);
            $this->tracking->updateTrackLead($inquiry->cookieSessionId, $lead->identifier);

            // Mark Track Unit as Inquired for Unit
            if ($inquiry->itemId) {
                $this->trackingUnit->markUnitInquired($inquiry->cookieSessionId, $inquiry->itemId, $inquiry->getUnitType());
            }
        }
    }


    /**
     * Choose Matching Lead
     *
     * @param Collection<Lead> $matches
     * @param array $params
     * @return null|Lead
     * @throws \App\Exceptions\PropertyDoesNotExists
     */
    private function chooseMatch(Collection $matches, array $params): ?Lead
    {
        // Sort Leads Into Standard or With Status
        $status = new Collection();
        $chosen = null;
        foreach ($matches as $lead) {
            if (!empty($lead->leadStatus)) {
                $status->push($lead);
            }
        }

        // Create Inquiry Lead
        $inquiry = new InquiryLead($params);

        // Find By Status!
        if (!empty($status) && count($status) > 0) {
            $chosen = $this->filterMatch($status, $inquiry);
        }

        // Still Not Chosen? Find Any!
        if (empty($chosen)) {
            $chosen = $this->filterMatch($matches, $inquiry);
        }

        // Return $result
        return $chosen;
    }

    /**
     * Filter Matching Lead
     *
     * @param Collection<Lead> $leads
     * @param InquiryLead $inquiry
     * @return null|Lead
     */
    private function filterMatch(Collection $leads, InquiryLead $inquiry): ?Lead
    {
        // Loop Status
        $chosen = null;
        $matches = new Collection();
        foreach ($leads as $lead) {
            // Find All Matches Between Both
            $matched = $inquiry->findMatches($lead);

            // Matched All 3
            if ($matched > InquiryLead::MERGE_MATCH_COUNT) {
                $chosen = $lead;
                break;
            }
            // Matched At Least 2
            elseif ($matched >= InquiryLead::MERGE_MATCH_COUNT) {
                $matches->push($lead);
            }
        }

        // Get First Match
        if (empty($chosen) && $matches->count() > 0) {
            $chosen = $matches->first();
        }

        // Return Chosen Lead
        return $chosen;
    }
}
