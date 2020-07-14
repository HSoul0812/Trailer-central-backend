<?php

namespace App\Repositories\CRM\User;

use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\NewDealerUser;

class SalesPersonRepository implements SalesPersonRepositoryInterface {
    
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
     * @param string $leadType
     * @param int
     */
    public function findNewestSalesPerson($dealerId, $dealerLocationId, $leadType) {
        // Last Sales Person Already Exists?
        if(isset($this->lastSalesPeople[$dealerId][$dealerLocationId][$leadType])) {
            return $this->lastSalesPeople[$dealerId][$dealerLocationId][$leadType];
        }

        // Find Newest Salesperson in DB
        $query = LeadStatus::select(LeadStatus::getTableName() . '.sales_person_id')
                            ->leftJoin(SalesPerson::getTableName(), SalesPerson::getTableName() . '.id', '=', LeadStatus::getTableName() . '.sales_person_id')
                            ->leftJoin(Lead::getTableName(), Lead::getTableName() . '.identifier', '=', LeadStatus::getTableName() . '.tc_lead_identifier')
                            ->where(Lead::getTableName() . '.dealer_id', $dealerId)
                            ->where(SalesPerson::getTableName() . '.is_' . $leadType, 1)
                            ->where(SalesPerson::getTableName() . '.sales_person_id', '<>', 0)
                            ->where(SalesPerson::getTableName() . '.sales_person_id', '<>', '')
                            ->whereNotNull(SalesPerson::getTableName() . '.sales_person_id');

        // Append Dealer Location
        if(!empty($dealerLocationId)) {
            $query = $query->where(SalesPerson::getTableName() . '.dealer_location_id', $dealerLocationId);
        }

        // Get Sales Person ID
        $salesPerson = $query->first();
        $salesPersonId = 0;
        if(!empty($salesPerson->sales_person_id)) {
            $salesPersonId = $salesPerson->sales_person_id;
        }

        // Set Sales Person ID
        return $this->setLastSalesPerson($dealerId, $dealerLocationId, $leadType, $salesPersonId);
    }

    /**
     * Find Next Sales Person
     * 
     * @param array $salesPeople
     * @param int $newestSalesPersonId
     * @param int $dealerLocationId
     * @param string $type
     * @return next sales person ID
     */
    private function findNextSalesPerson($salesPeople, $dealerLocationId, $type) {
        // Loop Sales People
        $validSalesPeople = [];
        $nextSalesPersonId = 0;
        $lastId = 0;
        foreach($salesPeople as $k => $salesPerson) {
            // Search By Location?
            if($dealerLocationId !== '0') {
                if($dealerLocationId !== $salesPerson->dealer_location_id) {
                    continue;
                }
            }

            // Search by Type?
            if($type !== NULL) {
                if($salesPerson->{'is_' . $type} !== '1') {
                    continue;
                }
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
                    $nextSalesPersonId = $salesPerson->id;
                    break;
                }
                $lastId = $salesPerson->id;
            }

            // Still No Next Sales Person?
            if(empty($nextSalesPersonId)) {
                $salesPerson = reset($validSalesPeople);
                $nextSalesPersonId = $salesPerson->id;
            }
        } elseif(count($validSalesPeople) === 1) {
            $salesPerson = reset($validSalesPeople);
            $nextSalesPersonId = $salesPerson->id;
        }

        // Still No Next Sales Person?
        if(empty($nextSalesPersonId)) {
            $nextSalesPersonId = $newestSalesPersonId;
        }

        // Return Next Sales Person ID
        return $nextSalesPersonId;
    }

    /**
     * Set Last Sales Person
     * 
     * @param int $dealerId
     * @param int $dealerLocationId
     * @param string $leadType
     * @param int $salesPersonId
     * @return int last sales person ID
     */
    public function setLastSalesPerson($dealerId, $dealerLocationId, $leadType, $salesPersonId) {
        // Assign to Arrays
        if(!isset($this->lastSalesPeople[$dealerId])) {
            $this->lastSalesPeople[$dealerId] = array();
        }
        if(!isset($this->lastSalesPeople[$dealerId][$dealerLocationId])) {
            $this->lastSalesPeople[$dealerId][$dealerLocationId] = array();
        }
        $this->lastSalesPeople[$dealerId][$dealerLocationId][$leadType] = $salesPersonId;

        // Dealer Location ID Isn't 0?!
        if(!empty($dealerLocationId)) {
            // ALSO Set for 0!
            if(!isset($this->lastSalesPeople[$dealerId][0])) {
                $this->lastSalesPeople[$dealerId][0] = array();
            }
            $this->lastSalesPeople[$dealerId][0][$leadType] = $salesPersonId;
        }

        // Return Last Sales Person ID
        return $this->lastSalesPeople[$dealerId][$dealerLocationId][$leadType];
    }

    /**
     * Find Sales Person Type
     * 
     * @param string $leadType
     * @return string
     */
    private function findSalesType($leadType) {
        // Set Default Lead Type
        if(in_array($leadType, SalesPerson::TYPE_DEFAULT) || empty($leadType)) {
            $leadType = 'default';
        }

        // Set Inventory Lead Type
        if(in_array($leadType, SalesPerson::TYPE_INVENTORY)) {
            $leadType = 'inventory';
        }

        // Not a Valid Type? Set Default!
        if(!in_array($leadType, SalesPerson::TYPE_VALID)) {
            $leadType = 'default';
        }

        // Return Lead Type!
        return $leadType;
    }
}
