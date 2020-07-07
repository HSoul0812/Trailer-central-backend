<?php

namespace App\Repositories\CRM\Interactions;

use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Interactions\Interaction;
use App\Models\User\NewDealerUser;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\Lead;
use App\Repositories\Traits\SortTrait;

class InteractionsRepository implements InteractionsRepositoryInterface {
    
    use SortTrait;
    
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
    
    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
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

    

}
