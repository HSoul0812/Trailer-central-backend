<?php

namespace App\Repositories\CRM\Leads;

use App\Models\Website\Website;
use App\Nova\Resources\Leads\WebsiteLead;
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
use App\Models\Inventory\Inventory;
use App\Repositories\Traits\SortTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
        // Create Lead
        return Lead::create($params);
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        return Lead::findOrFail($params['id']);
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
        $query = Lead::select(Lead::getTableName() . '.*')->with('inventory');

        if (isset($params['dealer_id'])) {
            $query = $query->where(Lead::getTableName() . '.dealer_id', $params['dealer_id']);
        }

        // Join Lead Status
        $query = $query->leftJoin(NewDealerUser::getTableName(), Lead::getTableName() . '.dealer_id', '=', NewDealerUser::getTableName() . '.id');
        $query = $query->leftJoin(LeadStatus::getTableName(), Lead::getTableName() . '.identifier', '=', LeadStatus::getTableName() . '.tc_lead_identifier');
        $query = $query->leftJoin(SalesPerson::getTableName(), function ($join) {
            $join->on(LeadStatus::getTableName() . '.sales_person_id', '=', SalesPerson::getTableName() . '.id')
                 ->on(SalesPerson::getTableName() . '.user_id', '=', NewDealerUser::getTableName() . '.user_id')
                 ->whereNull(SalesPerson::getTableName() . '.deleted_at');
        });

        // Require Sales Person ID NULL or 0
        $query = $query->whereNull(SalesPerson::getTableName() . '.id')
            ->where(Lead::getTableName() . '.is_archived', 0)
            ->where(Lead::getTableName() . '.is_spam', 0)
            ->whereRaw(Lead::getTableName() . '.date_submitted > CURDATE() - INTERVAL 30 DAY');

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['sort'])) {
            $query = $query->leftJoin(Interaction::getTableName(), Interaction::getTableName() . '.tc_lead_id',  '=', Lead::getTableName() . '.identifier');
            $query = $query->orderBy($this->sortOrders[$params['sort']]['field'], $this->sortOrders[$params['sort']]['direction']);
        }

        $query = $query->groupBy(Lead::getTableName() . '.identifier');

        // Return By Dealer?
        if($params['per_page'] === 'all') {
            return $query->orderBy(Lead::getTableName() . '.date_submitted', 'ASC')
                         ->orderBy(Lead::getTableName() . '.identifier', 'ASC')->get();
        }

        // Paginate!
        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Get Leads By Emails
     *
     * @param int $dealerId
     * @param array $emails
     * @return Collection of Lead
     */
    public function getByEmails(int $dealerId, array $emails) {
        // Return Lead Emails for User ID
        return Lead::select(['identifier', 'email_address'])
                     ->where('dealer_id', $dealerId)
                     ->where(function($query) use($emails) {
                         // Append Query
                         foreach($emails as $email) {
                             $query = $query->orWhere('email_address', '=', $email);
                         }
                     })->first();
    }

    public function update($params) {
        // Get Lead
        $lead = Lead::findOrFail($params['id']);

        // Update Lead
        DB::transaction(function() use (&$lead, $params) {
            $lead->fill($params)->save();
        });

        // Return Full Lead Details
        return $lead;
    }

    /**
     * Find Existing Leads That Matches Current Lead!
     *
     * @param array $params
     * @return Collection<Lead>
     */
    public function findAllMatches(array $params): Collection {
        // Clean Phones
        $params['phone1'] = preg_replace('/[-+)( x]+/', '', $params['phone_number'] ?? '');
        $params['phone2'] = '1' . $params['phone1'];
        if(strlen($params['phone1']) === 11) {
            $params['phone2'] = substr($params['phone1'], 1);
        }

        // Find Leads That Match Current!
        $lead = Lead::where('dealer_id', $params['dealer_id']);

        // Find Name
        return $lead->where(function(Builder $query) use($params) {
            return $query->where(function(Builder $query) use($params) {
                return $query->where('first_name', $params['first_name'])
                             ->where('last_name', $params['last_name']);
            })->orWhere(function(Builder $query) use($params) {
                return $query->whereRaw('REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`phone_number`, \'+\', \'\'), \'-\', \'\'), \'(\', \'\'), \')\', \'\'), \' \', \'\'), \'x\', \'\') = ?', $params['phone1'])
                             ->where('phone_number', '<>', '');
            })->orWhere(function(Builder $query) use($params) {
                return $query->whereRaw('REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`phone_number`, \'+\', \'\'), \'-\', \'\'), \'(\', \'\'), \')\', \'\'), \' \', \'\'), \'x\', \'\') = ?', $params['phone2'])
                             ->where('phone_number', '<>', '');
            })->orWhere(function(Builder $query) use($params) {
                return $query->where('email_address', $params['email_address'] ?? '')
                             ->where('email_address', '<>', '');
            });
        })->get();
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

        if (isset($filters['product_status'])) {
            $query = $this->addProductStatusToQuery($query, $filters['product_status']);
        }

        if(isset($filters['lead_source'])) {
            $query = $this->addLeadSourceToQuery($query, $filters['lead_source']);
        }

        return $query;
    }

    private function addLeadSourceToQuery($query, $leadSource) {
        if($leadSource === 'trailertraders') {
            $query->where(Lead::getTableName().'.website_id', '284');
        } elseif($leadSource === 'classifieds') {
            $query = $query->leftJoin(Website::getTableName().'.id',  '=', Lead::getTableName().'.website_id');
            $query->where(Website::getTableName().'.type', 'classified');
        }
    }

    private function addProductStatusToQuery($query, $productStatus) {
        if($productStatus == 'has_product') {
            return $query->where(Lead::getTableName().'.inventory_id', '>', 0);
        } else {
            return $query->where(Lead::getTableName().'.inventory_id', 0);
        }
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
        return $query->whereRaw("CONCAT(".Lead::getTableName().".first_name, ' ', ".Lead::getTableName().".last_name) LIKE ?", $customerName);
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

    /**
     * Find all leads without an associated customer record
     * note: this will skip all leads with matching dealer id, first name and last name
     * @param  callable|null  $callback
     * @param  int  $chunkSize
     * @return bool|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public function getLeadsWithoutCustomers(callable $callback = null, $chunkSize = 500)
    {
//        $query = Lead::query()
//            ->select('website_lead.identifier')
//            ->join('dealer', 'website_lead.dealer_id', '=', 'dealer.dealer_id')
//            ->leftJoinSub(Customer::query()->select(['id', 'dealer_id', 'first_name', 'last_name', 'website_lead_id']), 'customers', function($join) {
//                $join->on('customers.dealer_id', '=', 'website_lead.dealer_id')
//                    ->on('customers.first_name', '=', 'website_lead.first_name')
//                    ->on('customers.last_name', '=', 'website_lead.last_name')
//                ;
//            })
//            ->where('dealer.is_dms_active', '=', 1)
//            ->where('website_lead.is_spam', '=', 0)
//            ->where('website_lead.first_name', '<>', '')
//            ->whereNotNull('website_lead.first_name')
//            ->where('website_lead.last_name', '<>', '')
//            ->whereNotNull('website_lead.last_name')
//            ->where('customers.id', null);

        // get all website lead, where:
        //   dealer has dms active
        //   is_spam is 0
        //   first name and last name is not empty
        $query = Lead::query()
            ->select('website_lead.*')
            ->join('dealer', 'website_lead.dealer_id', '=', 'dealer.dealer_id')

            ->whereNull('website_lead.customer_id')
            ->where('dealer.is_dms_active', '=', 1)
            ->where('website_lead.is_spam', '=', 0)

            ->where('website_lead.first_name', '<>', '')
            ->whereNotNull('website_lead.first_name')

            ->where('website_lead.last_name', '<>', '')
            ->whereNotNull('website_lead.last_name');

        if ($callback !== null) {
            $query->chunkById($chunkSize, $callback);
            return true;
        } else {
            return $query->get();
        }
    }

    public function getMatches(int $dealerId, array $params)
    {
        $paramsCollect = collect($params['leads'] ?? null);

        if ($paramsCollect) {
            $query = Lead::query()
                ->where([
                    ['identifier', '>', 0],
                    ['dealer_id', '=', $dealerId],
                    ['lead_type', '!=', LeadType::TYPE_NONLEAD],
                    ['is_spam', '=', 0],
                ])
                ->where(function ($query) use ($paramsCollect) {
                    $query->whereIn('email_address', $paramsCollect->where('type', '=', 'email')->unique())
                        ->orWhereIn('phone_number', $paramsCollect->where('type', '=', 'phone')->unique())
                        ->orWhereIn('last_name', $paramsCollect->where('type', '=', 'last_name'));
                });

            return $query->get();
        }
    }
}
