<?php

namespace App\Repositories\CRM\User;

use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\User\NewDealerUser;

class SalesPersonRepository implements SalesPersonRepositoryInterface {

    /**
     * @var Array
     */
    private $salesPeople = [];

    /**
     * @var Array
     */
    private $lastSalesPeople = [];
    
    public function create($params) {
        throw NotImplementedException;
    }

    public function delete($params) {
        throw NotImplementedException;
    }

    public function get($params) {
        throw NotImplementedException;
    }

    /**
     * Get All Salespeople
     * 
     * @param int $params
     * @return type
     */
    public function getAll($params) {
        $query = SalesPerson::select('*');
        
        if (isset($params['dealer_id'])) {
            $newDealerUser = NewDealerUser::findOrFail($params['dealer_id']);
            $query = $query->where('user_id', $newDealerUser->user_id);
        }
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw NotImplementedException;
    }

    

    /**
     * Find Newest Sales Person From Vars or Check DB
     * 
     * @param int $dealerId
     * @param int $dealerLocationId
     * @param string $salesType
     * @param int
     */
    public function findNewestSalesPerson($dealerId, $dealerLocationId, $salesType) {
        // Last Sales Person Already Exists?
        if(isset($this->lastSalesPeople[$dealerId][$dealerLocationId][$salesType])) {
            return $this->lastSalesPeople[$dealerId][$dealerLocationId][$salesType];
        }

        // Find Newest Salesperson in DB
        $query = LeadStatus::select(LeadStatus::getTableName() . '.sales_person_id')
                            ->leftJoin(SalesPerson::getTableName(), SalesPerson::getTableName() . '.id', '=', LeadStatus::getTableName() . '.sales_person_id')
                            ->leftJoin(Lead::getTableName(), Lead::getTableName() . '.identifier', '=', LeadStatus::getTableName() . '.tc_lead_identifier')
                            ->where(Lead::getTableName() . '.dealer_id', $dealerId)
                            ->where(SalesPerson::getTableName() . '.is_' . $salesType, 1)
                            ->where(SalesPerson::getTableName() . '.id', '<>', 0)
                            ->where(SalesPerson::getTableName() . '.id', '<>', '')
                            ->whereNotNull(SalesPerson::getTableName() . '.id');

        // Append Dealer Location
        if(!empty($dealerLocationId)) {
            $query = $query->where(SalesPerson::getTableName() . '.dealer_location_id', $dealerLocationId);
        }

        // Get Sales Person ID
        echo $query->toSql();
        die;
        $salesPerson = $query->first();
        $salesPersonId = 0;
        if(!empty($salesPerson->sales_person_id)) {
            $salesPersonId = $salesPerson->sales_person_id;
        }

        // Set Sales Person ID
        $this->setLastSalesPerson($dealerId, $dealerLocationId, $salesType, $salesPersonId);

        // Return Sales Person
        return $salesPerson;
    }

    /**
     * Find Next Sales Person
     * 
     * @param int $dealerId
     * @param int $dealerLocationId
     * @param string $salesType
     * @param array $salesPeople
     * @return SalesPerson next sales person
     */
    public function findNextSalesPerson($dealerId, $dealerLocationId, $salesType, $salesPeople = array()) {
        // Get Sales Person ID
        $newestSalesPerson = $this->findNewestSalesPerson($dealerId, $dealerLocationId, $salesType);
        $newestSalesPersonId = 0;
        if(!empty($newestSalesPerson->id)) {
            $newestSalesPersonId = $newestSalesPerson->id;
        }

        // Don't Already Have SalesPeople?
        if(empty($salesPeople)) {
            // Get Sales People for Dealer ID
            $salesPeople = $this->findSalesPeople($dealerId);
        }

        // Loop Sales People
        $validSalesPeople = [];
        $nextSalesPerson = null;
        $lastId = 0;
        foreach($salesPeople as $k => $salesPerson) {
            // Search By Location?
            if($dealerLocationId !== 0 && $dealerLocationId !== '0') {
                if($dealerLocationId !== $salesPerson->dealer_location_id) {
                    continue;
                }
            }

            // Search by Type?
            if($salesPerson->{'is_' . $salesType} !== '1') {
                continue;
            }

            // Insert Valid Salespeople
            $validSalesPeople[] = $salesPerson;
        }

        // Loop Valid Sales People
        if(count($validSalesPeople) > 1) {
            $salesPerson = end($validSalesPeople);
            $lastId = $salesPerson->id;
            foreach($validSalesPeople as $salesPerson) {
                // Compare ID
                if($lastId === $newestSalesPersonId || $newestSalesPersonId === 0) {
                    $nextSalesPerson = $salesPerson;
                    break;
                }
                $lastId = $salesPerson->id;
            }

            // Still No Next Sales Person?
            if(empty($nextSalesPersonId)) {
                $salesPerson = reset($validSalesPeople);
                $nextSalesPerson = $salesPerson;
            }
        } elseif(count($validSalesPeople) === 1) {
            $salesPerson = reset($validSalesPeople);
            $nextSalesPerson = $salesPerson;
        }

        // Still No Next Sales Person?
        if(empty($nextSalesPerson)) {
            $nextSalesPerson = $newestSalesPerson;
        }

        // Set Next Salesperson
        $this->setLastSalesperson($dealerId, $dealerLocationId, $salesType, $nextSalesPerson->id);

        // Return Next Sales Person
        return $nextSalesPerson;
    }

    /**
     * Set Last Sales Person
     * 
     * @param int $dealerId
     * @param int $dealerLocationId
     * @param string $salesType
     * @param int $salesPersonId
     * @return int last sales person ID
     */
    public function setLastSalesPerson($dealerId, $dealerLocationId, $salesType, $salesPersonId) {
        // Assign to Arrays
        if(!isset($this->lastSalesPeople[$dealerId])) {
            $this->lastSalesPeople[$dealerId] = array();
        }
        if(!isset($this->lastSalesPeople[$dealerId][$dealerLocationId])) {
            $this->lastSalesPeople[$dealerId][$dealerLocationId] = array();
        }
        $this->lastSalesPeople[$dealerId][$dealerLocationId][$salesType] = $salesPersonId;

        // Dealer Location ID Isn't 0?!
        if(!empty($dealerLocationId)) {
            // ALSO Set for 0!
            if(!isset($this->lastSalesPeople[$dealerId][0])) {
                $this->lastSalesPeople[$dealerId][0] = array();
            }
            $this->lastSalesPeople[$dealerId][0][$salesType] = $salesPersonId;
        }

        // Return Last Sales Person ID
        return $this->lastSalesPeople[$dealerId][$dealerLocationId][$salesType];
    }

    /**
     * Find Sales People By Dealer ID
     * 
     * @param type $dealerId
     */
    public function findSalesPeople($dealerId) {
        // Already Exists?!
        if(isset($this->salesPeople[$dealerId])) {
            return $this->salesPeople[$dealerId];
        }

        // Get New Sales People By Dealer ID
        $newDealerUser = NewDealerUser::findOrFail($dealerId);
        $salesPeople = SalesPerson::select('*')->where('user_id', $newDealerUser->user_id)->all();

        // Set Sales People
        $this->salesPeople = array(
            'dealerId' => $salesPeople
        );

        // Return
        return $salesPeople;
    }

    /**
     * Find Sales Person Type
     * 
     * @param string $leadType
     * @return string
     */
    public function findSalesType($leadType) {
        // Set Default Lead Type
        $salesType = 'default';
        if(in_array($leadType, SalesPerson::TYPES_DEFAULT) || empty($leadType)) {
            $salesType = 'default';
        }

        // Set Inventory Lead Type
        if(in_array($leadType, SalesPerson::TYPES_INVENTORY)) {
            $salesType = 'inventory';
        }

        // Not a Valid Type? Set Default!
        if(!in_array($leadType, SalesPerson::TYPES_VALID)) {
            $salesType = 'default';
        }

        // Return Lead Type!
        return $salesType;
    }
}
