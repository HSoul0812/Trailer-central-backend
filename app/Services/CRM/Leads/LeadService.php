<?php

namespace App\Services\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Interactions\Interaction;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Leads\SourceRepositoryInterface;
use App\Repositories\CRM\Leads\TypeRepositoryInterface;
use App\Repositories\CRM\Leads\UnitRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class LeadService
 * 
 * @package App\Services\CRM\Leads
 */
class LeadService implements LeadServiceInterface
{
    /**
     * @var App\Repositories\CRM\Leads\LeadRepositoryInterface
     */
    protected $leads;

    /**
     * @var App\Repositories\CRM\Leads\StatusRepositoryInterface
     */
    protected $status;

    /**
     * @var App\Repositories\CRM\Leads\SourceRepositoryInterface
     */
    protected $sources;

    /**
     * @var App\Repositories\CRM\Leads\TypeRepositoryInterface
     */
    protected $types;

    /**
     * @var App\Repositories\CRM\Leads\UnitRepositoryInterface
     */
    protected $units;

    /**
     * @var App\Repositories\Inventory\InventoryRepositoryInterface
     */
    protected $inventory;

    /**
     * @var App\Repositories\CRM\Interactions\InteractionsRepositoryInterface
     */
    protected $interactions;

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
        InteractionsRepositoryInterface $interactions
    ) {
        // Initialize Repositories
        $this->leads = $leads;
        $this->status = $status;
        $this->sources = $sources;
        $this->types = $types;
        $this->units = $units;
        $this->inventory = $inventory;
        $this->interactions = $interactions;
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
        $types = new Collection();
        foreach($leadTypes as $leadType) {
            $type = $this->types->create([
                'lead_id' => $lead->identifier,
                'lead_type' => $leadType
            ]);
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
        $units = new Collection();
        foreach($inventoryIds as $inventoryId) {
            $unit = $this->units->create([
                'website_lead_id' => $lead->identifier,
                'inventory_id' => $inventoryId
            ]);
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
}