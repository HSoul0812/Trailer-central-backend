<?php

namespace App\Repositories\CRM\Leads;

use App\Models\CRM\User\Customer;
use App\Models\Website\Website;
use App\Exceptions\RepositoryInvalidArgumentException;
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
use App\Services\CRM\Leads\DTOs\LeadFiltersPopular;
use App\Utilities\TimeUtil;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;
use Carbon\Carbon;

class LeadRepository implements LeadRepositoryInterface {

    use SortTrait;

    private const LEAD_SOURCE_TRAILERTRADERS = 'trailertraders';
    private const LEAD_SOURCE_CLASSIFIEDS = 'classifieds';
    private const HAS_PRODUCT = 'has_product';

    private const AVAILABLE_INCLUDES = [
        'leadStatus',
        'interactions',
        'textLogs',
        'inventory',
        'fbUsers',
    ];

    private const DEFAULT_HOT_POTATO_DURATION = 30;

    private const POPULAR_FILTERS = [
        'all'              => ['label' => 'All'],
        'due_today'        => ['label' => 'Due Today', 'type' => 'interaction', 'time' => 'today'],
        'due_yesterday'    => ['label' => 'Due Yesterday', 'type' => 'interaction', 'time' => 'yesterday'],
        'due_week'         => ['label' => 'Due This Week', 'type' => 'interaction', 'time' => 'week'],
        'uncontacted'      => ['label' => 'Uncontacted', 'type' => 'interaction', 'time' => 'no'],
        'interacted_today' => ['label' => 'Interacted Today', 'type' => 'interacted', 'time' => 'today'],
        'interacted_week'  => ['label' => 'Interacted This Week', 'type' => 'interacted', 'time' => 'week']
    ];

    const SORT_ORDERS_CRM = [
        'no_due_past_due_future_due',
        'created_at',
        'future_due_past_due_no_due',
        'most_recent',
        '-most_recent',
        'status',
        '-created_at',
    ];

    private $sortOrders = [
        'id' => [
            'field' => 'website_lead.identifier',
            'direction' => 'DESC'
        ],
        '-id' => [
            'field' => 'website_lead.identifier',
            'direction' => 'ASC'
        ],
        'first_name' => [
            'field' => 'website_lead.first_name',
            'direction' => 'DESC'
        ],
        '-first_name' => [
            'field' => 'website_lead.first_name',
            'direction' => 'ASC'
        ],
        'last_name' => [
            'field' => 'website_lead.last_name',
            'direction' => 'DESC'
        ],
        '-last_name' => [
            'field' => 'website_lead.last_name',
            'direction' => 'ASC'
        ],
        'email' => [
            'field' => 'website_lead.email_address',
            'direction' => 'DESC'
        ],
        '-email' => [
            'field' => 'website_lead.email_address',
            'direction' => 'ASC'
        ],
        'created_at' => [
            [
                'field' => 'website_lead.dealer_id',
                'direction' => 'DESC'
            ],
            [
                'field' => 'website_lead.date_submitted',
                'direction' => 'DESC'
            ]
        ],
        '-created_at' => [
            [
                'field' => 'website_lead.dealer_id',
                'direction' => 'ASC'
            ],
            [
                'field' => 'website_lead.date_submitted',
                'direction' => 'ASC'
            ]
        ],
        'no_due_past_due_future_due' => [
            'field' => 'crm_tc_lead_status.next_contact_date',
            'direction' => 'ASC'
        ],
        'future_due_past_due_no_due' => [
            'field' => 'crm_tc_lead_status.next_contact_date',
            'direction' => 'DESC'
        ],
        '-most_recent' => [
            'field' => 'MIN(crm_interaction.interaction_time)',
            'direction' => 'ASC'
        ],
        'most_recent' => [
            'field' => 'MAX(crm_interaction.interaction_time)',
            'direction' => 'DESC'
        ],
        'status' => [
            'field' => 'crm_tc_lead_status.status',
            'direction' => 'ASC'
        ]
    ];

    private $sortOrdersNames = [
        'id' => [
            'name' => 'Identifier (A-Z)'
        ],
        '-id' => [
            'name' => 'Identifier (Z-A)'
        ],
        'first_name' => [
            'name' => 'First Name (A-Z)'
        ],
        '-first_name' => [
            'name' => 'First Name (Z-A)'
        ],
        'last_name' => [
            'name' => 'Last Name (A-Z)'
        ],
        '-last_name' => [
            'name' => 'Last Name (Z-A)'
        ],
        'email' => [
            'name' => 'E-Mail: (A-Z)'
        ],
        '-email' => [
            'name' => 'E-Mail: (Z-A)'
        ],
        'no_due_past_due_future_due' => [
            'name' => 'No Due Date, Past Due Dates, Future Due Date'
        ],
        'created_at' => [
            'name' => 'Most Recently Created'
        ],
        '-created_at' => [
            'name' => 'Least Recently Created'
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

    /**
     * Delete Lead
     *
     * @param array $params
     * @return Lead
     */
    public function delete($params) {
        
        if (isset($params['id'])) {
            $params['identifier'] = $params['id'];
            unset($params['id']);
        }

        return Lead::where($params)->delete();
    }

    public function get($params) {
        return Lead::findOrFail($params['id']);
    }

    public function first(array $params): ?Lead
    {
        if (empty($params['dealer_id'])) {
            throw new RepositoryInvalidArgumentException('Dealer Id is required');
        }

        $query = Lead::query();

        return $query->where('dealer_id', '=', $params['dealer_id'])->orderBy('date_submitted')->first();
    }

    public function getAll($params)
    {
        $query = Lead::query()->where([
                ['identifier', '>', 0],
                [Lead::getTableName().'.lead_type', '<>', LeadType::TYPE_NONLEAD],
        ]);

        if (isset($params['dealer_id'])) {
            $query = $query->where(Lead::getTableName().'.dealer_id', $params['dealer_id']);
        }
        /**
         * Filters
         */
        $query = $this->addFiltersToQuery($query, $params);

        /*
         * Due to several joins, some Lead fields are overwritten. That's why only necessary fields are specified in the select. Be careful with join
         */
        $query->select(Lead::getTableName() . '.*', LeadStatus::getTableName() . '.*', Interaction::getTableName() . '.*');

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        if (isset($params['include']) && is_string($params['include'])) {
            foreach (array_intersect(self::AVAILABLE_INCLUDES, explode(',', $params['include'])) as $include) {
                if ($include === 'interactions') {
                    $query = $query->with(['interactions' => function ($query) {
                        $query->with(['emailHistory', 'leadStatus' => function ($query) {
                            $query->with(['salesPerson']);
                        }]);
                    }]);
                } else {
                    $query = $query->with($include);
                }
            }
        }

        $query = $query->groupBy(Lead::getTableName().'.identifier');

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function getEdgeDate($params)
    {
        $query = Lead::selectRaw('MIN(website_lead.date_submitted) AS min_date_submitted,
                    MAX(website_lead.date_submitted) AS max_date_submitted')->where([
                ['identifier', '>', 0],
                [Lead::getTableName().'.lead_type', '<>', LeadType::TYPE_NONLEAD],
        ]);

        if (isset($params['dealer_id'])) {
            $query = $query->where(Lead::getTableName().'.dealer_id', $params['dealer_id']);
        }
        /**
         * Filters
         */
        $query = $this->addFiltersToQuery($query, $params);
        return $query->first();
    }

    /**
     * Get the earliest of submitted date between leads
     * 
     * @param array $leadIds
     * @param string 
     */
    public function getMinSubmittedDateBetweenLeads(array $leadIds)
    {
        return Lead::selectRaw('MIN(date_submitted) AS min_date_submitted')
            ->whereIn('identifier', $leadIds)->first()->min_date_submitted;
    }

    /**
     * Get the latest contact date between leads
     * 
     * @param array $leadIds
     * @return string
     */
    public function getMaxContactDateBetweenLeads(array $leadIds)
    {
        return LeadStatus::selectRaw('MAX(next_contact_date) AS max_contact_date')
            ->whereIn('tc_lead_identifier', $leadIds)->first()->max_contact_date;
    }

    /**
     * Get combined notes of leads
     * 
     * @param array $leadIds
     * @return string
     */
    public function getNotesBetweenLeads(array $leadIds)
    {
        // Get Notes for Various Leads
        $leads = Lead::select('note')
            ->whereIn('identifier', $leadIds)
            ->whereRaw('note is not null')
            ->whereRaw("trim(note) <> ''")
            ->get();

        // Get Notes
        $notes = '';
        foreach($leads as $lead) {
            if(!empty($notes)) {
                $notes .= PHP_EOL . PHP_EOL;
            }
            $notes .= $lead->note;
        }

        // Return Combined Notes
        return $notes;
    }

    /**
     * Get All Unassigned Leads
     *
     * @param int $params
     * @return type
     */
    public function getAllUnassigned($params) {
        $query = Lead::select(Lead::getTableName() . '.*')->with('inventory')
                     ->where('lead_type', '<>', LeadType::TYPE_NONLEAD);

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
            $query = $this->addSortQuery($query, $params['sort']);
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
     * Get All Unprocessed Leads
     *
     * @param int $params
     * @return Collection<Lead>
     */
    public function getAllUnprocessed(array $params): Collection {
        $query = Lead::select(Lead::getTableName() . '.*')->with('inventory')
                     ->where('lead_type', '<>', LeadType::TYPE_NONLEAD)
                     ->leftJoin(NewDealerUser::getTableName(), Lead::getTableName() . '.dealer_id', '=', NewDealerUser::getTableName() . '.id')
                     ->leftJoin(LeadStatus::getTableName(), Lead::getTableName() . '.identifier', '=', LeadStatus::getTableName() . '.tc_lead_identifier')
                     ->leftJoin(SalesPerson::getTableName(), function ($join) {
            $join->on(LeadStatus::getTableName() . '.sales_person_id', '=', SalesPerson::getTableName() . '.id')
                 ->on(SalesPerson::getTableName() . '.user_id', '=', NewDealerUser::getTableName() . '.user_id')
                 ->whereNull(SalesPerson::getTableName() . '.deleted_at');
        })->leftJoin(Interaction::getTableName(), function ($join) {
            $join->on(Lead::getTableName() . '.identifier', '=', Interaction::getTableName() . '.tc_lead_id')
                 ->on(Interaction::getTableName() . '.interaction_time', '>', LeadStatus::getTableName() . '.next_contact_date')
                 ->where(Interaction::getTableName() . '.interaction_type', 'EMAIL');
        })->whereNotNull(SalesPerson::getTableName() . '.email')
          ->where(Lead::getTableName() . '.is_archived', 0)
          ->where(Lead::getTableName() . '.is_spam', 0)
          ->whereNull(Interaction::getTableName() . '.interaction_time');

        if (isset($params['dealer_id'])) {
            $query = $query->where(Lead::getTableName() . '.dealer_id', $params['dealer_id']);
        }

        // Created At?
        if(!isset($params['first_created']) && !isset($params['last_created'])) {
            $query = $query->whereNotNull(LeadStatus::getTableName() . '.next_contact_date')
                           ->where(LeadStatus::getTableName() . '.next_contact_date', '<>', '0000-00-00 00:00:00');

            // Set First / Last Contact Date
            if(isset($params['first_contact'])) {
                $query = $query->where(LeadStatus::getTableName() . '.next_contact_date', '>=', $params['first_contact']);
            }
            if(isset($params['last_contact'])) {
                $query = $query->where(LeadStatus::getTableName() . '.next_contact_date', '<=', $params['last_contact']);
            }
        } elseif(isset($params['first_contact']) || isset($params['last_contact'])) {
            $query = $query->where(function($query) use($params) {
                return $query->whereNull(LeadStatus::getTableName() . '.next_contact_date')
                             ->orWhere(function($query) use($params) {
                    // Set First / Last Contact Date
                    if(isset($params['first_contact'])) {
                        $query = $query->where(LeadStatus::getTableName() . '.next_contact_date', '>=', $params['first_contact']);
                    }
                    if(isset($params['last_contact'])) {
                        $query = $query->where(LeadStatus::getTableName() . '.next_contact_date', '<=', $params['last_contact']);
                    }
                });
            })->orWhere(function($query) use($params) {
                // Set First / Last Created Date
                if(isset($params['first_created'])) {
                    $query = $query->where(Lead::getTableName() . '.date_submitted', '>=', $params['first_created']);
                }
                if(isset($params['last_created'])) {
                    $query = $query->where(Lead::getTableName() . '.date_submitted', '<=', $params['last_created']);
                }
            });
        }

        // Set Sort Query
        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        // Return Results
        return $query->groupBy(Lead::getTableName() . '.identifier')
                     ->orderBy(Lead::getTableName() . '.date_submitted', 'ASC')
                     ->orderBy(Lead::getTableName() . '.identifier', 'ASC')->get();
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
                     ->where('lead_type', '<>', LeadType::TYPE_NONLEAD)
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
        $lead = Lead::where('dealer_id', $params['dealer_id'])
                    ->where('lead_type', '<>', LeadType::TYPE_NONLEAD);

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
        $query = Lead::select('*')->where('lead_type', '<>', LeadType::TYPE_NONLEAD);

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

    public function getLeadsSortFieldsCrm(): array
    {
        return array_values(array_filter($this->getSortFields(), function ($item) {
            return in_array($item['param'], self::SORT_ORDERS_CRM);
        }));
    }

    protected function getSortOrderNames() {
        return $this->sortOrdersNames;
    }

    /** 
     * Get Mapped Array of CRM Sorts > Name
     * 
     * @return array
     */
    public function getSortOrderNamesCrm(): array
    {
        // Initialize Sorts
        $sorts = [];
        foreach($this->getLeadsSortFieldsCrm() as $sort) {
            $param = $sort['param'];
            if(isset($this->sortOrdersNames[$param])) {
                $sorts[$param] = $this->sortOrdersNames[$param]['name'];
            }
        }

        // Return Sorts => Names Mapping
        return $sorts;
    }

    /** 
     * Get Popular Filters
     * 
     * @return array<LeadFiltersPopular>
     */
    public function getPopularFilters(): array
    {
        // Return Popular Filters Containing Preset Times
        $filters = [];
        foreach(self::POPULAR_FILTERS as $filter) {
            $filters[] = LeadFiltersPopular::fill($filter);
        }

        // Return array<LeadFiltersPopular>
        return $filters;
    }

    private function getHotLeadsByDealer($dealerId, $params = []) {
        $user = User::findOrFail($dealerId);

        $hotLeadsQuery = $user->leads()
                        ->leftJoin(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier')
                        ->where(LeadStatus::getTableName().'.status', Lead::STATUS_HOT)
                        ->distinct();

        $hotLeadsQuery = $this->addFiltersToQuery($hotLeadsQuery, $params, true);

        return $hotLeadsQuery->count('identifier');
    }

    private function getLostLeadsByDealer($dealerId, $params = []) {
        $user = User::findOrFail($dealerId);

        $lostLeadsQuery = $user->leads()
                        ->leftJoin(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier')
                        ->where(LeadStatus::getTableName().'.status', Lead::STATUS_LOST)
                        ->distinct();

        $lostLeadsQuery = $this->addFiltersToQuery($lostLeadsQuery, $params, true);

        return $lostLeadsQuery->count('identifier');
    }

    private function getOpenLeadsbyDealer($dealerId, $params = []) {
        $user = User::findOrFail($dealerId);


        $openLeadsQuery = $user->leads()
                            ->leftJoin(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier')
                            ->where(function($query) {
                                $query->whereNotIn(LeadStatus::getTableName().'.status', [Lead::STATUS_WON, Lead::STATUS_WON_CLOSED, Lead::STATUS_LOST])
                                    ->orWhereNull(LeadStatus::getTableName().'.status');
                            })
                            ->distinct();

        $openLeadsQuery = $this->addFiltersToQuery($openLeadsQuery, $params, true);

        return $openLeadsQuery->count('identifier');
    }

    private function getWonLeadsByDealer($dealerId, $params = []) {
        $user = User::findOrFail($dealerId);

        $wonLeadsQuery = $user->leads()
                            ->leftJoin(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier')
                            ->whereIn(LeadStatus::getTableName().'.status', [Lead::STATUS_WON, Lead::STATUS_WON_CLOSED])
                            ->distinct();

        $wonLeadsQuery = $this->addFiltersToQuery($wonLeadsQuery, $params, true);

        return $wonLeadsQuery->count('identifier');
    }

    /**
     * @param Builder|Relation $query
     * @return Builder|Relation
     */
    private function joinInteraction($query) {
        return $query->leftJoin(
            Interaction::getTableName(),
            Interaction::getTableName() . '.tc_lead_id',
            '=',
            Lead::getTableName() . '.identifier'
        );
    }

    private function addFiltersToQuery($query, $filters, $noStatusJoin = false, $noInteractionJoin = false) {
        if (!$noStatusJoin) {
            $query = $query->leftJoin(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier');
        }

        if (!$noInteractionJoin) {
            $this->joinInteraction($query);
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

        if (isset($filters['next_contact_from'])) {
            $query = $this->addNextContactFromToQuery($query, TimeUtil::convertTimeFormat($filters['next_contact_from'], TimeUtil::REQUEST_TIME_FORMAT, TimeUtil::MYSQL_TIME_FORMAT));
        }

        if (isset($filters['next_contact_to'])) {
            $query = $this->addNextContactToToQuery($query, TimeUtil::convertTimeFormat($filters['next_contact_to'], TimeUtil::REQUEST_TIME_FORMAT, TimeUtil::MYSQL_TIME_FORMAT));
        }

        if (isset($filters['interacted_from'])) {
            $query = $this->addInteractedFromToQuery($query, TimeUtil::convertTimeFormat($filters['interacted_from'], TimeUtil::REQUEST_TIME_FORMAT, TimeUtil::MYSQL_TIME_FORMAT));
        }

        if (isset($filters['interacted_to'])) {
            $query = $this->addInteractedToToQuery($query, TimeUtil::convertTimeFormat($filters['interacted_to'], TimeUtil::REQUEST_TIME_FORMAT, TimeUtil::MYSQL_TIME_FORMAT));
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

        if (!empty($filters['lead_type'])) {
            $query = $this->addLeadTypeToQuery($query, $filters['lead_type']);
        }

        if (isset($filters['product_status'])) {
            $query = $this->addProductStatusToQuery($query, $filters['product_status']);
        }

        if(isset($filters['lead_source'])) {
            $query = $this->addLeadSourceToQuery($query, $filters['lead_source']);
        }

        if(!isset($filters['is_spam'])) {
            $filters['is_spam'] = 0;
        }
        if($filters['is_spam'] !== -1) {
            $query = $query->where(Lead::getTableName() . '.is_spam', '=', $filters['is_spam']);
        }

        return $query;
    }

    /**
     * @param Builder $query
     * @param string $leadSource
     * @return Builder
     */
    private function addLeadSourceToQuery(Builder $query, string $leadSource) {
        if($leadSource === self::LEAD_SOURCE_TRAILERTRADERS) {
            $query->where(Lead::getTableName().'.website_id', '284');
        } else if ($leadSource === self::LEAD_SOURCE_CLASSIFIEDS) {
            $query
                ->leftJoin(Website::getTableName() . '.id', '=', Lead::getTableName() . '.website_id')
                ->where(Website::getTableName() . '.type', Lead::LEAD_TYPE_CLASSIFIED);
        }
        return $query;
    }

    /**
     * @param Builder $query
     * @param string $productStatus
     * @return Builder
     */
    private function addProductStatusToQuery(Builder $query, string $productStatus) {
        return $query->where(
            Lead::getTableName().'.inventory_id',
            $productStatus === self::HAS_PRODUCT ? '>' : '=',
            0
        );
    }

    /**
     * @param Builder|Relation $query
     * @param string $dateTo
     * @return Builder
     */
    private function addDateToToQuery($query, string $dateTo) {
         return $query->where(Lead::getTableName().'.date_submitted', '<=', $dateTo);
    }

    /**
     * @param Builder|Relation $query
     * @param string $dateFrom
     * @return Builder
     */
    private function addDateFromToQuery($query, string $dateFrom) {
        return $query->where(Lead::getTableName().'.date_submitted', '>=', $dateFrom);
    }

    private function addNextContactFromToQuery($query, string $dateFrom) {
        return $query->where(LeadStatus::getTableName().'.next_contact_date', '>=', $dateFrom);
    }

    private function addNextContactToToQuery($query, string $dateTo) {
        return $query->where(LeadStatus::getTableName().'.next_contact_date', '<=', $dateTo);
    }

    private function addInteractedFromToQuery($query, string $dateFrom) {
        return $query->where(Interaction::getTableName().'.interaction_time', '>=', $dateFrom);
    }

    private function addInteractedToToQuery($query, string $dateTo) {
        return $query->where(Interaction::getTableName().'.interaction_time', '<=', $dateTo);
    }

    /**
     * @param Builder|Relation $query
     * @param string $search
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    private function addSearchToQuery($query, string $search) {
        $query = $query->leftJoin(Inventory::getTableName(), Inventory::getTableName().'.inventory_id',  '=', Lead::getTableName().'.inventory_id');

        $leadTableName = Lead::getTableName();

        return $query->where(function($q) use ($search, $leadTableName) {
            $q->where(Lead::getTableName().'.title', 'LIKE', '%' . $search . '%')
                    ->orWhere(Lead::getTableName().'.first_name', 'LIKE', '%' . $search . '%')
                    ->orWhere(Lead::getTableName().'.last_name', 'LIKE', '%' . $search . '%')
                    ->orWhereRaw("CONCAT_WS('', REPLACE({$leadTableName}.first_name, ' ', ''), REPLACE({$leadTableName}.last_name, ' ', '')) LIKE ?", '%' . str_replace(' ', '', $search) . '%')
                    ->orWhere(Lead::getTableName().'.email_address', 'LIKE', '%' . $search . '%')
                    ->orWhere(Inventory::getTableName().'.title', 'LIKE', '%' . $search . '%')
                    ->orWhere(Inventory::getTableName().'.stock', 'LIKE', '%' . $search . '%');

            if (preg_match('/[0-9]{6,}/', str_replace([' ', '-', '(', ')'], '', $search))) {
                $q->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE($leadTableName.phone_number, ' ', ''), '-', ''), '(', ''), ')', '') LIKE ?", '%' . str_replace([' ', '-', '(', ')'], '', $search) . '%');
            }
        });
    }

    /**
     * @param Builder|Relation $query
     * @param bool $isArchived
     * @return Builder|Relation
     */
    private function addIsArchivedToQuery($query, bool $isArchived) {
        if (!$isArchived) {
            return $query->where(Lead::getTableName().'.is_archived', '!=', 1);
        }

        return $query->where(Lead::getTableName().'.is_archived', $isArchived);
    }

    /**
     * @param Builder|Relation $query
     * @param int $location
     * @return Builder|Relation
     */

    private function addLocationToQuery($query, int $location) {
        return $query->where(Lead::getTableName().'.dealer_location_id', $location);
    }

    /**
     * @param Builder|Relation $query
     * @param string $customerName
     * @return Builder|Relation
     */

    private function addCustomerNameToQuery($query, string $customerName) {
        return $query->whereRaw("CONCAT(".Lead::getTableName().".first_name, ' ', ".Lead::getTableName().".last_name) LIKE ?", $customerName);
    }

    /**
     * @param Builder|Relation $query
     * @param int $salesPersonId
     * @return Builder|Relation
     */
    private function addSalesPersonIdToQuery($query, int $salesPersonId) {
        if ($salesPersonId === 0) {
            return $query->whereNull(LeadStatus::getTableName().'.sales_person_id');
        }

        return $query->where(LeadStatus::getTableName().'.sales_person_id', $salesPersonId);
    }

    /**
     * @param Builder|Relation $query
     * @param array $leadStatus
     * @return Builder|Relation
     */
    private function addLeadStatusToQuery($query, array $leadStatus)
    {
        // If Uncontacted, add whereNull
        if(in_array(Lead::STATUS_UNCONTACTED, $leadStatus)) {
            return $query->where(function(Builder $q) use ($leadStatus) {
                return $q->whereIn(LeadStatus::getTableName() . '.status', $leadStatus)
                         ->orWhere(LeadStatus::getTableName() . '.status', 'open')
                         ->orWhere(LeadStatus::getTableName() . '.status', '')
                         ->orWhereNull(LeadStatus::getTableName() . '.status');
            });
        }

        // Return Normal Standalone IN
        return $query->whereIn(LeadStatus::getTableName() . '.status', $leadStatus);
    }

    /**
     * @param Builder|Relation $query
     * @param array $leadType
     * @return Builder|Relation
     */
    private function addLeadTypeToQuery($query, array $leadType)
    {
        $leadTypeTableName = LeadType::getTableName();

        return $query->leftJoin(
            $leadTypeTableName,
            $leadTypeTableName . '.lead_id',
            '=',
            Lead::getTableName() . '.identifier'
        )->where(function ($query) use ($leadType, $leadTypeTableName) {
            $query->whereIn($leadTypeTableName . '.lead_type', $leadType)
                  ->orWhereIn(Lead::getTableName().'.lead_type', $leadType);
        });
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
            ->where('website_lead.lead_type', '<>', LeadType::TYPE_NONLEAD)

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

    /**
     * Get Matches for LeadRepository
     *
     * @param int $dealerId
     * @param array $params
     * @return Collection<Lead>
     */
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
                    $query
                        ->whereIn('email_address', $paramsCollect->where('type', '=', 'email')->unique()->map(function ($c) {
                            return collect($c)->forget('type');
                        }))
                        ->orWhereIn('phone_number', $paramsCollect->where('type', '=', 'phone')->unique()->map(function ($c) {
                            return collect($c)->forget('type');
                        }))
                        ->orWhereIn('last_name', $paramsCollect->where('type', '=', 'last_name')->map(function ($c) {
                            return collect($c)->forget('type');
                        }));
                });

            return $query->get();
        }
    }

    /**
     * Get Unique Full Names
     *
     * @param array $params
     * @return \Illuminate\Support\Collection|LengthAwarePaginator
     */
    public function getUniqueFullNames(array $params)
    {
        if (empty($params['dealer_id'])) {
            throw new RepositoryInvalidArgumentException('Dealer Id is required');
        }

        $query = DB::table(Lead::getTableName());

        $query = $query->selectRaw('DISTINCT TRIM(first_name) AS first_name, TRIM(last_name) AS last_name');

        $query = $query->where('dealer_id', '=', $params['dealer_id']);

        $query = $query->where(function (\Illuminate\Database\Query\Builder $query) {
            $query->whereRaw('TRIM(`first_name`) <> \'\'')
                ->orWhereRaw('TRIM(`last_name`) <> \'\'');
        });

        $query = $query->where('is_spam', '=', 0);

        if (isset($params['is_archived'])) {
            $query = $query->where('is_archived', '=', $params['is_archived']);
        }

        if (!empty($params['search_term'])) {
            $query->whereRaw('CONCAT(first_name,\' \', last_name) LIKE ?', ['%' . trim($params['search_term']) . '%']);
        }

        $query = $query->orderByRaw('TRIM(`first_name`), TRIM(`last_name`)');

        if (!empty($params['per_page'])) {
            return $query->paginate($params['per_page'])->appends($params);
        }

        return $query->get();
    }

    /**
     * @throws \Exception
     */
    public function createLeadFromCustomer(Customer $customer)
    {
        if(!is_null($customer->website_lead_id)) {
            throw new \Exception("Customer id $customer->id already have a Lead.");
        }

        $websiteId = data_get($customer, 'dealer.website.id');

        if(is_null($websiteId)) {
            throw new \Exception("Customer id $customer->id (dealer id $customer->dealer_id) doesn't have a website.");
        }

        /** @var Lead $lead */
        $lead = Lead::create([
            'website_id' => $websiteId,
            'dealer_id' => $customer->dealer_id,
            'lead_type' => LeadType::TYPE_NONLEAD,
            'first_name' => $customer->first_name,
            'middle_name' => $customer->middle_name,
            'last_name' => $customer->last_name,
            'email_address' => $customer->email,
            'address' => $customer->address,
            'city' => $customer->city,
            'zip' => $customer->postal_code,
            'phone_number' => $customer->cell_phone,
        ]);

        // Update website_lead_id of this customer
        $customer->website_lead_id = $lead->identifier;
        $customer->save();
    }

    public function output($params)
    {
        $query = DB::table(Lead::getTableName());

        $query = $query->select([
            Lead::getTableName().'.email_address',
            Lead::getTableName().'.phone_number',
            Lead::getTableName().'.preferred_contact',
            Lead::getTableName().'.first_name',
            Lead::getTableName().'.last_name',
            Lead::getTableName().'.lead_type',
            LeadStatus::getTableName().'.source',
            Lead::getTableName().'.address',
            Lead::getTableName().'.city',
            Lead::getTableName().'.state',
            Lead::getTableName().'.zip',
            LeadStatus::getTableName().'.status',
            LeadStatus::getTableName().'.closed_at',
            Lead::getTableName().'.comments',
            Lead::getTableName().'.date_submitted',
        ]);

        $query = $query->leftJoin(Website::getTableName(), Lead::getTableName().'.website_id', '=', Website::getTableName().'.id');
        $query = $query->leftJoin(Inventory::getTableName(), Lead::getTableName().'.inventory_id', '=', Inventory::getTableName().'.inventory_id');

        $query = $query->where(Lead::getTableName().'.identifier', '>', 0);
        // add filters if any
        $query = $this->addFiltersToQuery($query, $params);

        if (isset($params['dealer_id'])) {
            $query = $query->where(Lead::getTableName().'.dealer_id', $params['dealer_id']);
        }

        $query = $query->groupBy(Lead::getTableName().'.identifier');
        // sorting
        $sort = 'created_at';
        if (isset($params['sort'])) {
            $sort = $params['sort'];
        }
        $query = $this->addSortQuery($query, $sort);

        $records = $query->get();

        $csv = Writer::createFromString();

        // insert the header
        $csv->insertOne([
            'Email',
            'Phone',
            'Preferred Contact',
            'First Name',
            'Last Name',
            'Lead Type',
            'Lead Source',
            'Address',
            'City',
            'State',
            'Zip',
            'Status',
            'Closed Date',
            'Comments',
            'Submission Date'
        ]);

        foreach ($records as $record) {
            $csv->insertOne((array) $record);
        }

        return $csv->toString(); //returns the CSV document as a string
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}
