<?php

namespace App\Repositories\CRM\Interactions;

use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\Lead;
use App\Repositories\Traits\SortTrait;

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
            'name' => 'Newest Tasks to Oldest Tasks, then Future Tasks',
        ],
        '-created_at' => [
            'name' => 'Oldest Tasks to Newest Tasks, then Future Tasks',
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
        // Get User ID
        $lead = Lead::findOrFail($params['lead_id']);
        $params['tc_lead_id'] = $lead->identifier;
        $params['user_id'] = $lead->newDealerUser->user_id;

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
                 'from_email'])->where('tc_lead_id', $params['lead_id']);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if(!isset($params['sort'])) {
            $params['sort'] = '-created_at';
        }

        if (!isset($params['include_texts']) || !empty($params['include_texts'])) {
            $query = $this->addTextUnion($query, $params);
        }

        $query = $this->addSortQuery($query, $params['sort']);
        
        return $query->paginate($params['per_page'])->appends($params);
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

    public function getTasksByDealerId($dealerId, $sort = '-created_at', $perPage = 15) {
        $query = Interaction::select('*');       

        $query->leftJoin(LeadStatus::getTableName(), LeadStatus::getTableName().'.tc_lead_identifier', '=', Interaction::getTableName().'.tc_lead_id');
        $query->join(Lead::getTableName(), Lead::getTableName().'.identifier', '=', Interaction::getTableName().'.tc_lead_id');
        
        $query->where(Lead::getTableName().'.dealer_id', $dealerId);
        $query->where('interaction_time', 'not like', "0000%");
        $query->whereRaw(Interaction::getTableName().'.interaction_type =' . LeadStatus::getTableName() . '.contact_type');
        
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
            DB::raw('"" AS sent_by')
        ])->where('lead_id', $params['lead_id']);

        // Initialize TextLog Query
        return $query->union($textLog);
    }
}