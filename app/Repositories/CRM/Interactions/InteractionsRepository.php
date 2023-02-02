<?php

namespace App\Repositories\CRM\Interactions;

use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Interactions\InteractionEmail;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\SalesPerson;
use App\Repositories\Traits\SortTrait;
use Illuminate\Database\Eloquent\Collection;

class InteractionsRepository implements InteractionsRepositoryInterface {
    
    use SortTrait;

    /**
     * @var EmailHistoryRepositoryInterface
     */
    private $emailHistory;
    
    private $sortOrders = [
        'created_at' => [
            'field' => 'interaction_time',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'interaction_time',
            'direction' => 'ASC'
        ]
    ];
    
    private $sortOrdersNames = [
        'created_at' => [
            'name' => 'Newest Tasks to Oldest Tasks',
        ],
        '-created_at' => [
            'name' => 'Oldest Tasks to Newest Tasks',
        ],
    ];

    /**
     * InteractionsRepository constructor.
     * 
     * @param EmailHistoryRepositoryInterface
     */
    public function __construct(EmailHistoryRepositoryInterface $emailHistory) {
        $this->emailHistory = $emailHistory;
    }
    
    public function create($params) {
        if (!empty($params['lead_id'])) {
            // Get User ID
            $lead = Lead::findOrFail($params['lead_id']);
            $params['tc_lead_id'] = $lead->identifier;
            $params['user_id'] = $lead->newDealerUser->user_id;
        }

        // Create Interaction
        return Interaction::create($params);
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        return Interaction::findOrFail($params['id']);
    }

    /**
     * Get All Interactions
     * 
     * @param array $params
     * @return Collection EmailHistory
     */
    public function getAll($params) {
        // Get User ID
        $query = Interaction::select(
                ['interaction_id', 
                 'lead_product_id', 
                 'tc_lead_id', 
                 'user_id', 
                 'interaction_type',
                 'interaction_notes',
                 'interaction_time',
                 'sent_by',
                 'from_email',
                  DB::raw('"" AS to_no')])->where('tc_lead_id', $params['lead_id']);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if(!isset($params['sort'])) {
            $params['sort'] = 'created_at';
        }

        if (!isset($params['include_texts']) || !empty($params['include_texts'])) {
            $query = $this->addTextUnion($query, $params);
        }

        $query = $this->addSortQuery($query, $params['sort']);
        
        return $query->paginate($params['per_page'])->appends($params);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getFirst10(array $params) : Collection {
        // Get User ID
        $query = Interaction::select(
                ['interaction_id', 
                 'lead_product_id', 
                 'tc_lead_id', 
                 'user_id', 
                 'interaction_type',
                 'interaction_notes',
                 'interaction_time',
                 'sent_by',
                 'from_email',
                  DB::raw('"" AS to_no')])->where('tc_lead_id', $params['lead_id']);

        $query->limit(10);

        if (!isset($params['include_texts']) || !empty($params['include_texts'])) {
            $query = $this->addTextUnion($query, $params);
        }

        $query = $this->addSortQuery($query, 'created_at');
        
        return $query->get();
    }

    public function update($params) {
        $interaction = Interaction::findOrFail($params['id']);

        DB::transaction(function() use (&$interaction, $params) {
            // Fix Lead ID
            $params['tc_lead_id'] = $params['lead_id'];

            // Fill Interaction Details
            $interaction->fill($params)->save();
        });

        return $interaction;
    }

    /**
     * @param array $data Data to replace
     * @param array $where Condition
     * @return int Total data affected
     */
    public function batchUpdate(array $data, array $where) {
        
        return Interaction::where($where)->update($data);
    }

    /**
     * Create or Update Interaction
     * 
     * @param array $params
     * @return EmailHistory
     */
    public function createOrUpdate($params) {
        // ID Exists?!
        if(isset($params['id'])) {
            $interaction = Interaction::find($params['id']);

            // Interaction Exists?!
            if(!empty($interaction->interaction_id)) {
                // Update Interaction
                return $this->update($params);
            }
        }

        // Create Interaction
        return $this->create($params);
    }

    /**
     * Create InteractionEmail
     * 
     * @param array $params
     * @return InteractionEmail
     */

    public function createInteractionEmail($params) {
        return InteractionEmail::create($params);
    }

    public function getTasksByDealerId($dealerId, $sort = '-created_at', $perPage = 15) {
        $query = Interaction::select('*');

        $query->leftJoin(Lead::getTableName(), Interaction::getTableName().".tc_lead_id", "=", Lead::getTableName().".identifier");
        $query->leftJoin(LeadStatus::getTableName(), LeadStatus::getTableName(). ".tc_lead_identifier", "=", Interaction::getTableName().".tc_lead_id");
        
        $query->where(Lead::getTableName().'.dealer_id', $dealerId);
        $query->where('interaction_time', 'not like', "0000%");
        $query->whereRaw(Interaction::getTableName().'.interaction_type =' . LeadStatus::getTableName() . '.contact_type');
        $query->where(Interaction::getTableName(). ".is_closed", 0);
        $query->where(Lead::getTableName(). ".is_archived", 0);
        
        if(empty($sort)) {
            $sort = '-created_at';
        }
        $query = $this->addSortQuery($query, $sort);

        return $query->paginate($perPage)->appends(['per_page' => $perPage]);       
    }

    public function getTasksBySalespersonId($salespersonId, $sort = '-created_at', $perPage = 15) {

        $query = Interaction::select('*');

        $query->leftJoin(Lead::getTableName(), Interaction::getTableName().".tc_lead_id", "=", Lead::getTableName().".identifier");
        $query->leftJoin(LeadStatus::getTableName(), LeadStatus::getTableName(). ".tc_lead_identifier", "=", Interaction::getTableName().".tc_lead_id");
        $query->leftJoin(SalesPerson::getTableName(), function($join) {
            $join->on(LeadStatus::getTableName().".sales_person_id", "=", SalesPerson::getTableName().".id")
                ->whereNull(SalesPerson::getTableName().".deleted_at");
        });

        $query->where(SalesPerson::getTableName(). ".id", $salespersonId);
        $query->where('interaction_time', 'not like', "0000%");
        $query->whereRaw(Interaction::getTableName(). ".interaction_type = ". LeadStatus::getTableName() .".contact_type");
        $query->where(Interaction::getTableName(). ".is_closed", 0);
        $query->where(Lead::getTableName(). ".is_archived", 0);

        if(empty($sort)) {
            $sort = '-created_at';
        }
        $query = $this->addSortQuery($query, $sort);
        
        return $query->paginate($perPage)->appends(['per_page' => $perPage]);  
    }
    
    public function getTasksSortFields() {
        return $this->getSortFields();
    }
    
    protected function getSortOrderNames() {
        return $this->sortOrdersNames;
    }
    
    protected function getSortOrders() {
        return $this->sortOrders;
    }

    /**
     * Add Text to Interaction Query
     * 
     * @param QueryBuilder $query
     * @param array $params
     * @return QueryBuilder
     */
    private function addTextUnion($query, $params) {
        // Get User ID
        $lead = Lead::findOrFail($params['lead_id']);

        // Initialize TextLog Object
        $textLog = TextLog::select([
            'id AS interaction_id',
            DB::raw('0 AS lead_product_id'),
            'lead_id AS tc_lead_id',
            DB::raw($lead->newDealerUser->user_id . ' AS user_id'),
            DB::raw('"TEXT" AS interaction_type'),
            'log_message AS interaction_notes',
            'date_sent AS interaction_time',
            DB::raw('from_number AS from_email'),
            DB::raw('to_number AS to_no'),
            DB::raw('"" AS sent_by')
        ])->where('lead_id', $params['lead_id']);

        // Initialize TextLog Query
        return $query->union($textLog);
    }
}