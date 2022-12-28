<?php

namespace App\Services\CRM\Leads;

use App\Exceptions\CRM\Leads\MergeLeadsException;
use App\Models\CRM\Interactions\Facebook\Message;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Interactions\Interaction;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\CRM\Interactions\Facebook\MessageRepositoryInterface;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Leads\FacebookRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Leads\SourceRepositoryInterface;
use App\Repositories\CRM\Leads\TypeRepositoryInterface;
use App\Repositories\CRM\Leads\UnitRepositoryInterface;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\Dms\QuoteRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Traits\Repository\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class LeadService
 *
 * @package App\Services\CRM\Leads
 */
class LeadService implements LeadServiceInterface
{
    use Transaction;

    /**
     * @var LeadRepositoryInterface
     */
    protected $leads;

    /**
     * @var StatusRepositoryInterface
     */
    protected $status;

    /**
     * @var SourceRepositoryInterface
     */
    protected $sources;

    /**
     * @var TypeRepositoryInterface
     */
    protected $types;

    /**
     * @var UnitRepositoryInterface
     */
    protected $units;

    /**
     * @var InventoryRepositoryInterface
     */
    protected $inventory;

    /**
     * @var InteractionsRepositoryInterface
     */
    protected $interactions;

    /**
     * @var MessageRepositoryInterface
     */
    protected $fbMessageRepository;

    /**
     * @var EmailHistoryRepositoryInterface
     */
    protected $emailHistoryRepository;

    /**
     * @var FacebookRepositoryInterface
     */
    protected $facebookRepository;

    /**
     * @var TextRepositoryInterface
     */
    protected $textRepository;

    /**
     * @var QuoteRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * LeadService constructor.
     */
    public function __construct(
        LeadRepositoryInterface $leads,
        StatusRepositoryInterface $status,
        SourceRepositoryInterface $sources,
        TypeRepositoryInterface $types,
        UnitRepositoryInterface $units,
        InventoryRepositoryInterface $inventory,
        InteractionsRepositoryInterface $interactions,
        MessageRepositoryInterface $fbMessageRepository,
        EmailHistoryRepositoryInterface $emailHistoryRepository,
        FacebookRepositoryInterface $facebookRepository,
        TextRepositoryInterface $textRepository,
        QuoteRepositoryInterface $quoteRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        // Initialize Repositories
        $this->leads = $leads;
        $this->status = $status;
        $this->sources = $sources;
        $this->types = $types;
        $this->units = $units;
        $this->inventory = $inventory;
        $this->interactions = $interactions;
        $this->fbMessageRepository = $fbMessageRepository;
        $this->emailHistoryRepository = $emailHistoryRepository;
        $this->facebookRepository = $facebookRepository;
        $this->textRepository = $textRepository;
        $this->quoteRepository = $quoteRepository;
        $this->customerRepository = $customerRepository;
    }


    /**
     * Create Lead
     *
     * @param array $rawParams
     * @return Lead
     */
    public function create(array $rawParams): Lead {
        // Fix Params
        $params = $this->fixCleanParams($rawParams);

        // Create Lead
        $lead = $this->leads->create($params);

        // Create or Update Status
        $params['lead_id'] = $lead->identifier;
        $status = $this->status->create($params);
        $lead->setRelation('leadStatus', $status);

        // Override Fixes
        if (isset($params['lead_source']) && !empty($lead->newDealerUser->user_id)) {
            // Send Lead Source
            $this->sources->createOrUpdate([
                'user_id' => $lead->newDealerUser->user_id,
                'source_name' => $params['lead_source']
            ]);
        }

        // Update Lead Types
        if (isset($params['lead_types'])) {
            $this->updateLeadTypes($lead, $params['lead_types']);
        }

        // Update Units of Inventory
        if (isset($params['inventory'])) {
            $this->updateUnitsOfInterest($lead, $params['inventory']);
        }

        // Create Customer if Not Exist
        $this->customerRepository->createFromLead($lead);

        // Return Full Lead Details
        return $lead;
    }

    /**
     * Update Lead
     *
     * @param array $rawParams
     * @return Lead
     */
    public function update(array $rawParams): Lead {
        // Fix Params
        $params = $this->fixCleanParams($rawParams);

        // Start Transaction
        $lead = null;
        DB::transaction(function() use (&$lead, $params) {
            // Update Lead
            $lead = $this->leads->update($params);
            $params = $this->appendRelationParams($lead, $params);

            // Create or Update Status
            $params['lead_id'] = $lead->identifier;
            $status = $this->status->createOrUpdate($params);
            $lead->setRelation('leadStatus', $status);

            // Override Fixes
            if (isset($params['lead_source'])) {
                // Send Lead Source
                $this->sources->createOrUpdate([
                    'user_id' => $lead->newDealerUser->user_id,
                    'source_name' => $params['lead_source']
                ]);
            }

            // Update Lead Types
            $this->updateLeadTypes($lead, $params['lead_types']);

            // Update Units of Interest
            $this->updateUnitsOfInterest($lead, $params['inventory']);
        });

        // Return Full Lead Details
        return $lead;
    }

    /**
     * Merge Lead
     *
     * @param Lead $lead
     * @param array $params
     * @return Interaction
     */
    public function merge(Lead $lead, array $params): Interaction {
        // Configure Notes From Provided Data
        $notes = '';
        if(!empty($params['first_name'])) {
            $notes .= $params['first_name'];
        }
        if(!empty($params['last_name'])) {
            if(!empty($notes)) {
                $notes .= ' ';
            }
            $notes .= $params['last_name'];
        }
        if(!empty($notes)) {
            $notes .= '<br /><br />';
        }

        // Add Phone/Email
        if(!empty($params['phone_number'])) {
            $notes .= 'Phone: ' . $params['phone_number'] . '<br /><br />';
        }
        if(!empty($params['email_address'])) {
            $notes .= 'Email: ' . $params['email_address'] . '<br /><br />';
        }
        if(!empty($params['comments'])) {
            $notes .= $params['comments'];
        }

        // Get Interaction Data
        return $this->interactions->create([
            'lead_id' => $lead->identifier,
            'interaction_type'   => 'INQUIRY',
            'interaction_notes'  => !empty($notes) ? 'Original Inquiry: ' . $notes : 'Not Provided'
        ]);
    }

    /**
     * Delete Existing Lead Types and Insert New Ones
     *
     * @param Lead $lead
     * @param array $leadTypes
     * @return Collection<LeadType>
     */
    private function updateLeadTypes(Lead $lead, array $leadTypes) {
        // Nothing to Update
        if (empty($leadTypes)) {
            return collect([]);
        }

        // Delete Existing Lead Types!
        $this->types->delete(['lead_id' => $lead->identifier]);

        // Loop Lead Types
        $added = [];
        $types = new Collection();
        foreach($leadTypes as $leadType) {
            if(in_array($leadType, $added)) {
                continue;
            }
            $type = $this->types->create([
                'lead_id' => $lead->identifier,
                'lead_type' => $leadType
            ]);
            $added[] = $leadType;
            $types->push($type);
        }

        // Set Lead Types to Lead
        $lead->setRelation('leadTypes', $types);

        // Return Array of Lead Types
        return $types;
    }

    /**
     * Delete Existing Units of Interest and Insert New Ones
     *
     * @param Lead $lead
     * @param array $inventoryIds
     * @return Collection<InventoryLead>
     */
    private function updateUnitsOfInterest(Lead $lead, array $inventoryIds) {
        // Nothing to Update
        if (empty($inventoryIds)) {
            return collect([]);
        }

        // Delete Existing Units of Interest!
        $this->units->delete(['website_lead_id' => $lead->identifier]);

        // Loop Lead Types
        $added = [];
        $units = new Collection();
        foreach($inventoryIds as $inventoryId) {
            if(in_array($inventoryId, $added)) {
                continue;
            }
            $unit = $this->units->create([
                'website_lead_id' => $lead->identifier,
                'inventory_id' => $inventoryId
            ]);
            $added[] = $inventoryId;
            $units->push($unit);
        }

        // Get Inventory
        $inventory = $this->inventory->getAll([
            'dealer_id' => $lead->dealer_id,
            InventoryRepositoryInterface::CONDITION_AND_WHERE_IN => [
                'inventory_id' => $inventoryIds
            ]
        ]);

        // Set Units of Interest to Lead
        $lead->setRelation('units', $inventory);

        // Return Array of Inventory Lead
        return $units;
    }

    /**
     * Clean Lead Types/Units of Interest Params
     *
     * @param array $params
     * @return array
     */
    private function fixCleanParams(array $params) {
        // Get First Lead Type
        if(isset($params['lead_types']) && is_array($params['lead_types'])) {
            $params['lead_type'] = reset($params['lead_types']);
        } elseif(isset($params['lead_type'])) {
            $params['lead_types'] = [$params['lead_type']];
        }

        // Fix Units of Interest
        if(isset($params['inventory']) && is_array($params['inventory'])) {
            $params['inventory_id'] = reset($params['inventory']);
        } elseif(isset($params['inventory_id'])) {
            $params['inventory'] = [$params['inventory_id']];
        }

        // Fix Preferred Contact
        if(empty($params['preferred_contact'])) {
            $params['preferred_contact'] = 'phone';
            if(empty($params['phone_number']) && !empty($params['email_address'])) {
                $params['preferred_contact'] = 'email';
            }
        }

        // Return Params
        return $params;
    }

    /**
     * Append Relation Params
     *
     * @param Lead $lead
     * @param array $params
     * @return type
     */
    private function appendRelationParams(Lead $lead, array $params) {
        // Fix Lead Types
        if(empty($params['lead_types'])) {
            $params['lead_types'] = [];
        }
        if(!in_array($lead->lead_type, $params['lead_types'])) {
            $params['lead_type'] = reset($params['lead_types']);
        }

        // Fix Inventory
        if(empty($params['inventory'])) {
            $params['inventory'] = [];
        }
        if(!in_array($lead->inventory_id, $params['inventory'])) {
            $params['inventory_id'] = reset($params['inventory']);
        }

        // Return Params
        return $params;
    }

    /**
     * Convert FB User Into Lead
     *
     * @param array $params
     * @return Lead
     */
    public function assign(array $params): Lead
    {
        $lead = $this->update($params);

        $params['first_name'] = $lead->first_name;
        $params['last_name'] = $lead->last_name;
        $params['phone_number'] = $lead->phone_number;
        $params['email_address'] = $lead->phone_number;

        if ($lead->fbLead && $lead->fbLead->conversation) {
            /** @var Collection<Message> $messages */
            $messages = $lead->fbLead->conversation->messages;

            // Loop Messages
            if (!$messages->isEmpty()) {
                foreach($messages as $message) {
                    // Create Interaction
                    $interaction = $this->interactions->create([
                        'lead_id' => $lead->identifier,
                        'interaction_type' => Interaction::TYPE_FB,
                        'interaction_notes' => $message->message,
                        'interaction_time' => $message->created_at
                    ]);

                    // Add Interaction ID
                    $this->fbMessageRepository->update([
                        'message_id' => $message->message_id,
                        'interaction_id' => $interaction->interaction_id
                    ]);
                }
            }
        }

        return $lead;
    }

    /**
     * Get Matches for Lead
     *
     * @param array $params
     * @return Collection<Lead>
     */
    public function getMatches(array $params)
    {
        return $this->leads->getMatches($params['dealer_id'], $params);
    }

    /**
     * @param int $leadId
     * @param int $mergesLeadId
     * @return bool
     * @throws MergeLeadsException
     */
    public function mergeLeads(int $leadId, int $mergesLeadId): bool
    {
        $params = [
            'lead_id' => $leadId,
            'search' => ['lead_id' => $mergesLeadId]
        ];

        $customerParams = [
            'website_lead_id' => $leadId,
            'search' => ['website_lead_id' => $mergesLeadId]
        ];

        try {
            $this->beginTransaction();

            $this->emailHistoryRepository->bulkUpdate($params);

            $this->facebookRepository->bulkUpdateFbLead($params);

            $this->textRepository->bulkUpdate($params);

            $this->quoteRepository->bulkUpdate($params);

            $this->customerRepository->bulkUpdate($customerParams);

            $this->commitTransaction();

            Log::info('leads has been successfully merged', ['leadId' => $leadId, 'mergesLeadId' => $mergesLeadId]);

        } catch (\Exception $e) {
            Log::error('Merge leads error. Message - ' . $e->getMessage() , $e->getTrace());
            $this->rollbackTransaction();

            throw new MergeLeadsException('Merge leads error');
        }

        return true;
    }
}
