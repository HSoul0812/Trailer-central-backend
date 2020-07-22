<?php

namespace App\Repositories\CRM\Leads;

use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadAssign;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\NewDealerUser;
use App\Models\User\User;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\Leads\LeadSource;
use App\Models\Inventory\Inventory;
use App\Repositories\Traits\SortTrait;
use Illuminate\Support\Facades\DB;

class LeadRepository implements LeadRepositoryInterface {

    use SortTrait;
    
    private $sortOrders = [
        'no_due_past_due_future_due' => [
            'field' => 'crm_tc_lead_status.next_contact_date',
            'direction' => 'ASC'
        ],
        'created_at' => [
            'field' => 'website_lead.date_submitted',
            'direction' => 'DESC'
        ],
        'future_due_past_due_no_due' => [
            'field' => 'crm_tc_lead_status.next_contact_date',
            'direction' => 'DESC'
        ],
        '-most_recent' => [
            'field' => 'crm_interaction.interaction_time',
            'direction' => 'ASC'
        ],
        'most_recent' => [
            'field' => 'crm_interaction.interaction_time',
            'direction' => 'DESC'
        ],
        'status' => [
            'field' => 'crm_tc_lead_status.status',
            'direction' => 'ASC'
        ]
    ];
    
    private $sortOrdersNames = [
        'no_due_past_due_future_due' => [
            'name' => 'No Due Date, Past Due Dates, Future Due Date'
        ],
        'created_at' => [
            'name' => 'Most Recently Created'
        ],
        'future_due_past_due_no_due' => [
            'name' => 'Future Due Dates, Past Due Dates, No Due Date'
        ],
        '-most_recent' => [
            'name' => 'Least Recent Interaction to Most Recent'
        ],
        'most_recent' => [
            'name' => 'Most Recent Interaction to Least Recent'
        ],
        'status' => [
            'name' => 'Status'
        ]
    ];
    
    
    public function create($params) {
        // Get First Lead Type
        if(isset($params['lead_types']) && is_array($params['lead_types'])) {
            $params['lead_type'] = reset($params['lead_types']);
        }

        // Create Lead
        $lead = Lead::create($params);
        $leadStatus = null;
        $leadSource = null;

        if (isset($params['lead_types'])) {
            $this->updateLeadTypes($lead->identifier, $params['lead_types']);
        }

        if (isset($params['next_contact_date'])) {
            $leadStatus = $leadSource = LeadStatus::create([
                'status' => empty($params['lead_status']) ? LeadStatus::STATUS_UNCONTACTED : $params['lead_status'],
                'tc_lead_identifier' => $lead->identifier,
                'next_contact_date' => $params['next_contact_date'],
                'contact_type' => LeadStatus::TYPE_CONTACT,
                'source' => empty($params['lead_source']) ? null : $params['lead_source']
            ]);
        }
        
        if (isset($params['lead_status']) && empty($leadStatus)) {
            $leadStatus = $leadSource = LeadStatus::create([
                'status' => $params['lead_status'],
                'tc_lead_identifier' => $lead->identifier,
                'source' => empty($params['lead_source']) ? null : $params['lead_source']
            ]);
        }
        
        if (isset($params['lead_source']) && empty($leadSource)) {
            LeadStatus::create([
                'source' => $params['lead_source'],
                'tc_lead_identifier' => $lead->identifier
            ]);
        }
        
        return $lead;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        $query = Lead::where('identifier', '>', 0);

        if (isset($params['dealer_id'])) {
            $query = $query->where(Lead::getTableName().'.dealer_id', $params['dealer_id']);
        }
        
        /**
         * Filters
         */
        $query = $this->addFiltersToQuery($query, $params);
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        
        if (isset($params['sort'])) {
            $query = $query->leftJoin(Interaction::getTableName(), Interaction::getTableName().'.tc_lead_id',  '=', Lead::getTableName().'.identifier');
            $query = $query->orderBy($this->sortOrders[$params['sort']]['field'], $this->sortOrders[$params['sort']]['direction']);
        }
        
        $query = $query->groupBy(Lead::getTableName().'.identifier');
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Get All Unassigned Leads
     * 
     * @param int $params
     * @return type
     */
    public function getAllUnassigned($params) {
        $query = Lead::select(Lead::getTableName().'.*')->with('inventory');

        if (isset($params['dealer_id'])) {
            $query = $query->where(Lead::getTableName().'.dealer_id', $params['dealer_id']);
        }

        // Join Lead Status
        $query = $query->leftJoin(NewDealerUser::getTableName(), Lead::getTableName().'.dealer_id', '=', NewDealerUser::getTableName().'.id');
        $query = $query->leftJoin(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier');
        $query = $query->leftJoin(SalesPerson::getTableName(), function ($join) {
            $join->on(LeadStatus::getTableName().'.sales_person_id', '=', SalesPerson::getTableName().'.id')
                 ->on(SalesPerson::getTableName().'.user_id', '=', NewDealerUser::getTableName().'.user_id');
        });

        // Require Sales Person ID NULL or 0
        $query = $query->where(function($query) {
            $query->whereNull(LeadStatus::getTableName().'.sales_person_id')
                  ->orWhere(LeadStatus::getTableName().'.sales_person_id', 0);
        })->where(Lead::getTableName().'.is_archived', 0)
          ->where(Lead::getTableName().'.is_spam', 0)
          ->whereRaw(Lead::getTableName().'.date_submitted > CURDATE() - INTERVAL 30 DAY');
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        
        if (isset($params['sort'])) {
            $query = $query->leftJoin(Interaction::getTableName(), Interaction::getTableName().'.tc_lead_id',  '=', Lead::getTableName().'.identifier');
            $query = $query->orderBy($this->sortOrders[$params['sort']]['field'], $this->sortOrders[$params['sort']]['direction']);
        }
        
        $query = $query->groupBy(Lead::getTableName().'.identifier');

        // Return By Dealer?
        if($params['per_page'] === 'all') {
            return $query->get();
        }

        // Paginate!
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        $lead = Lead::findOrFail($params['id']);
        
        DB::transaction(function() use (&$lead, $params) {
            $leadStatusUpdates = [];

            if (isset($params['lead_status'])) {
                $leadStatusUpdates['status'] = $params['lead_status'];
            }

            if (isset($params['next_contact_date'])) {
                $leadStatusUpdates['next_contact_date'] = $params['next_contact_date'];
            }

            if (isset($params['contact_type'])) {
                $leadStatusUpdates['contact_type'] = $params['contact_type'];
            }

            if (isset($params['sales_person_id'])) {
                $leadStatusUpdates['sales_person_id'] = $params['sales_person_id'];
            }

            if (!empty($leadStatusUpdates)) {
                if ($lead->leadStatus) {
                    $lead->leadStatus()->update($leadStatusUpdates);
                } else {
                    if (empty($leadStatusUpdates['status'])) {
                        $leadStatusUpdates['status'] = Lead::STATUS_UNCONTACTED;
                    }
                    $lead->leadStatus()->create($leadStatusUpdates);
                }
            }

            // Process Lead Types
            if(isset($params['lead_types']) && is_array($params['lead_types'])) {
                if(!in_array($lead->lead_type, $params['lead_types'])) {
                    $params['lead_type'] = reset($params['lead_types']);
                }

                // Create Lead Types
                $this->updateLeadTypes($lead->identifier, $params['lead_types']);
            }
            
            $lead->fill($params)->save();

        });
        
        return $lead;
    }

    /**
     * Delete Existing Lead Types and Insert New Ones
     * 
     * @param int $leadId
     * @param array $leadTypes
     * @return array of LeadType
     */
    public function updateLeadTypes($leadId, $leadTypes) {
        // Delete Existing Lead Types!
        LeadType::whereLeadId($leadId)->delete();

        // Initialize Lead Type
        $leadTypeModels = array();
        DB::transaction(function() use (&$leadTypeModels, $leadId, $leadTypes) {
            // Loop Lead Types
            foreach($leadTypes as $leadType) {
                $leadTypeModels[] = LeadType::create([
                    'lead_id' => $leadId,
                    'lead_type' => $leadType
                ]);
            }
        });

        // Return Array of Lead Types
        return $leadTypeModels;
    }

    /**
     * Create Assign Log for Lead
     * 
     * @param type $params
     * @return type
     */
    public function assign($params) {
        // Fix Explanation!
        if(isset($params['explanation']) && is_array($params['explanation'])) {
            $params['explanation'] = implode("\n\n", $params['explanation']);
        }

        return LeadAssign::create($params);
    }
    
    public function getCustomers($params = []) {
        $query = Lead::select('*');
        
        if (isset($params['dealer_id'])) {
            $query = $query->where(Lead::getTableName().'.dealer_id', $params['dealer_id']);
        }
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        
        /**
         * Filters
         */
        $query = $this->addFiltersToQuery($query, $params);

        $query = $query->where('first_name', '!=', '');
        $query = $query->where('last_name', '!=', '');        
        $query = $query->where('is_spam', 0);
        
        $query = $query->groupByRaw('first_name, last_name');
        
        $query = $query->orderBy('first_name', 'DESC');
        $query = $query->orderBy('last_name', 'DESC');
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Get All Lead Statuses
     * 
     * @return type
     */
    public function getStatuses() { 
        return [
            [ 
                'id' => Lead::STATUS_HOT,
                'name' => Lead::STATUS_HOT
            ],
            [ 
                'id' => Lead::STATUS_COLD,
                'name' => Lead::STATUS_COLD
            ],
            [ 
                'id' => Lead::STATUS_LOST,
                'name' => Lead::STATUS_LOST
            ],
            [ 
                'id' => Lead::STATUS_MEDIUM,
                'name' => Lead::STATUS_MEDIUM
            ],
            [ 
                'id' => Lead::STATUS_NEW_INQUIRY,
                'name' => Lead::STATUS_NEW_INQUIRY
            ],
            [ 
                'id' => Lead::STATUS_UNCONTACTED,
                'name' => Lead::STATUS_UNCONTACTED
            ],
            [ 
                'id' => Lead::STATUS_WON,
                'name' => Lead::STATUS_WON
            ],
            [ 
                'id' => Lead::STATUS_WON_CLOSED,
                'name' => Lead::STATUS_WON_CLOSED
            ]
        ];
    }

    /**
     * Get All Lead Types
     * 
     * @return type
     */
    public function getTypes() {
        return [
            [ 
                'id' => LeadType::TYPE_BUILD,
                'name' => ucfirst(LeadType::TYPE_BUILD)
            ],
            [ 
                'id' => LeadType::TYPE_CALL,
                'name' => ucfirst(LeadType::TYPE_CALL)
            ],
            [ 
                'id' => LeadType::TYPE_GENERAL,
                'name' => ucfirst(LeadType::TYPE_GENERAL)
            ],
            [ 
                'id' => LeadType::TYPE_CRAIGSLIST,
                'name' => ucfirst(LeadType::TYPE_CRAIGSLIST)
            ],
            [ 
                'id' => LeadType::TYPE_INVENTORY,
                'name' => ucfirst(LeadType::TYPE_INVENTORY)
            ],
            [ 
                'id' => LeadType::TYPE_TEXT,
                'name' => ucfirst(LeadType::TYPE_TEXT)
            ],
            [ 
                'id' => LeadType::TYPE_SHOWROOM_MODEL,
                'name' => ucfirst(LeadType::TYPE_SHOWROOM_MODEL)
            ],
            [ 
                'id' => LeadType::TYPE_JOTFORM,
                'name' => ucfirst(LeadType::TYPE_JOTFORM)
            ],
            [ 
                'id' => LeadType::TYPE_RENTALS,
                'name' => ucfirst(LeadType::TYPE_RENTALS)
            ],
            [ 
                'id' => LeadType::TYPE_FINANCING,
                'name' => ucfirst(LeadType::TYPE_FINANCING)
            ],
            [ 
                'id' => LeadType::TYPE_SERVICE,
                'name' => ucfirst(LeadType::TYPE_SERVICE)
            ],
            [ 
                'id' => LeadType::TYPE_TRADE,
                'name' => ucfirst(LeadType::TYPE_TRADE)
            ]
        ]; 
    }

    /**
     * Get All Default Lead Sources
     * 
     * @return type
     */
    public function getSources() {
        return LeadSource::select(['lead_source_id AS id', 'source_name AS name'])
                         ->where('user_id', 0)
                         ->orderBy('lead_source_id', 'ASC')
                         ->get();
    }

    /**
     * Get All Supported States
     * 
     * @return array of supported states on leads
     */
    public function getStates() {
        return Lead::STATES_LIST;
    }

    /**
     * Get Lead Status Counts By Dealer
     * 
     * @param type $dealerId
     * @param type $params
     * @return type
     */
    public function getLeadStatusCountByDealer($dealerId, $params = []) {                    
        return [
            'won' => $this->getWonLeadsByDealer($dealerId, $params),
            'open' => $this->getOpenLeadsbyDealer($dealerId, $params),
            'lost' => $this->getLostLeadsByDealer($dealerId, $params),
            'hot' => $this->getHotLeadsByDealer($dealerId, $params)
        ];
    }
    
    public function getLeadsSortFields() {
        return $this->getSortFields();
    }
    
    protected function getSortOrderNames() {
        return $this->sortOrdersNames;
    }
    
    private function getHotLeadsByDealer($dealerId, $params = []) {
        $user = User::findOrFail($dealerId);
        
        if (!isset($params['is_archived'])) {
            $params['is_archived'] = Lead::NOT_ARCHIVED;
        }
        
        $hotLeadsQuery = $user->leads()
                        ->join(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier')
                        ->where(LeadStatus::getTableName().'.status', Lead::STATUS_HOT);
        
        $hotLeadsQuery = $this->addFiltersToQuery($hotLeadsQuery, $params, true);
        
        return $hotLeadsQuery->count();
    }
    
    private function getLostLeadsByDealer($dealerId, $params = []) {
        $user = User::findOrFail($dealerId);
        
        if (!isset($params['is_archived'])) {
            $params['is_archived'] = Lead::NOT_ARCHIVED;
        }
        
        $lostLeadsQuery = $user->leads()
                        ->join(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier')
                        ->where(LeadStatus::getTableName().'.status', Lead::STATUS_LOST);
        
        $lostLeadsQuery = $this->addFiltersToQuery($lostLeadsQuery, $params, true);
        
        return $lostLeadsQuery->count();
    }
    
    private function getOpenLeadsbyDealer($dealerId, $params = []) {
        $user = User::findOrFail($dealerId);
        
        if (!isset($params['is_archived'])) {
            $params['is_archived'] = Lead::NOT_ARCHIVED;
        }
        
        $openLeadsQuery = $user->leads()
                            ->join(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier')
                            ->whereNotIn(LeadStatus::getTableName().'.status', [Lead::STATUS_WON, Lead::STATUS_WON_CLOSED, Lead::STATUS_LOST]);
        
        $openLeadsQuery = $this->addFiltersToQuery($openLeadsQuery, $params, true);
        
        return $openLeadsQuery->count();
    }
    
    private function getWonLeadsByDealer($dealerId, $params = []) {
        $user = User::findOrFail($dealerId);
        
        if (!isset($params['is_archived'])) {
            $params['is_archived'] = Lead::NOT_ARCHIVED;
        }
        
        $wonLeadsQuery = $user->leads()
                            ->join(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier')
                            ->whereIn(LeadStatus::getTableName().'.status', [Lead::STATUS_WON, Lead::STATUS_WON_CLOSED]);
        
        $wonLeadsQuery = $this->addFiltersToQuery($wonLeadsQuery, $params, true);
        
        return $wonLeadsQuery->count();
    }
    
    private function addFiltersToQuery($query, $filters, $noStatusJoin = false) {
        if (!$noStatusJoin) {
            $query = $query->leftJoin(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier');    
        }        
        
        if (isset($filters['search_term'])) {            
            $query = $this->addSearchToQuery($query, $filters['search_term']);
        } 
        
        if (isset($filters['date_from'])) {
            $query = $this->addDateFromToQuery($query, $filters['date_from']);           
        }
        
        if (isset($filters['date_to'])) {
            $query = $this->addDateToToQuery($query, $filters['date_to']);        
        }
        
        if (isset($filters['is_archived'])) {
            $query = $this->addIsArchivedToQuery($query, $filters['is_archived']);
        }
        
        if (isset($filters['location'])) {
            $query = $this->addLocationToQuery($query, $filters['location']);
        }
        
        if (isset($filters['customer_name'])) {
            $query = $this->addCustomerNameToQuery($query, $filters['customer_name']);          
        }
        
        if (isset($filters['sales_person_id'])) {  
            $query = $this->addSalesPersonIdToQuery($query, $filters['sales_person_id']);
        }
        
        if (isset($filters['lead_status'])) {
            $query = $this->addLeadStatusToQuery($query, $filters['lead_status']);
        }  

        if (isset($filters['lead_type'])) {
            $query = $this->addLeadTypeToQuery($query, $filters['lead_type']);
        }      
        
        return $query;
    }
    
    private function addDateToToQuery($query, $dateTo) {        
         return $query->where(Lead::getTableName().'.date_submitted', '<=', $dateTo);    
    }
    
    private function addDateFromToQuery($query, $dateFrom) {
        return $query->where(Lead::getTableName().'.date_submitted', '>=', $dateFrom);
    }
    
    private function addSearchToQuery($query, $search) {
        $query = $query->leftJoin(Inventory::getTableName(), Inventory::getTableName().'.inventory_id',  '=', Lead::getTableName().'.inventory_id');
                
        return $query->where(function($q) use ($search) {
            $q->where(Lead::getTableName().'.title', 'LIKE', '%' . $search . '%')
                    ->orWhere(Lead::getTableName().'.first_name', 'LIKE', '%' . $search . '%')
                    ->orWhere(Lead::getTableName().'.last_name', 'LIKE', '%' . $search . '%')
                    ->orWhere(Lead::getTableName().'.email_address', 'LIKE', '%' . $search . '%')
                    ->orWhere(Lead::getTableName().'.phone_number', 'LIKE', '%' . $search . '%')
                    ->orWhere(Inventory::getTableName().'.title', 'LIKE', '%' . $search . '%')
                    ->orWhere(Inventory::getTableName().'.stock', 'LIKE', '%' . $search . '%');

        });
    }
    
    private function addIsArchivedToQuery($query, $isArchived) {
        return $query->where(Lead::getTableName().'.is_archived', $isArchived);
    }
    
    private function addLocationToQuery($query, $location) {
        return $query->where(Lead::getTableName().'.dealer_location_id', $location);
    }   
    
    private function addCustomerNameToQuery($query, $customerName) {
        return $query->whereRaw("CONCAT(".Lead::getTableName().".first_name, ' ', ".Lead::getTableName().".last_name)", $customerName);   
    }  
    
    private function addSalesPersonIdToQuery($query, $salesPersonId) {
        return $query->where(LeadStatus::getTableName().'.sales_person_id', $salesPersonId);
    }
        
    private function addLeadStatusToQuery($query, $leadStatus) {
        return $query->whereIn(LeadStatus::getTableName().'.status', $leadStatus);
    }
        
    private function addLeadTypeToQuery($query, $leadType) {
        $query = $query->leftJoin(LeadType::getTableName(), LeadType::getTableName().'.lead_id',  '=', Lead::getTableName().'.identifier');
        return $query->whereIn(LeadType::getTableName().'.lead_type', $leadType);
    }

}
