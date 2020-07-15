<?php

namespace App\Repositories\CRM\User;

use App\Repositories\Repository;
use App\Utilities\JsonApi\RequestQueryable;

interface SalesPersonRepositoryInterface extends Repository, RequestQueryable {

    /**
     * Generate a salesperson sales report;
     * Each row indicates sales person, customer, date amount, cost
     * @param $params
     * @return mixed
     */
    public function salesReport($params);

    /**
     * Find Newest Sales Person From Vars or Check DB
     * 
     * @param int $dealerId
     * @param int $dealerLocationId
     * @param string $salesType
     * @param SalesPerson
     */
    public function findNewestSalesPerson($dealerId, $dealerLocationId, $salesType);

    /**
     * Round Robin to Next Sales Person
     * 
     * @param int $dealerId
     * @param int $dealerLocationId
     * @param string $salesType
     * @param SalesPerson $newestSalesPerson
     * @param array $salesPeople
     * @return SalesPerson next sales person
     */
    public function roundRobinSalesPerson($dealerId, $dealerLocationId, $salesType, $newestSalesPerson);

    /**
     * Preserve the Round Robin Sales Person Temporarily
     * 
     * @param int $dealerId
     * @param int $dealerLocationId
     * @param string $salesType
     * @param int $salesPersonId
     * @return int last sales person ID
     */
    public function setRoundRobinSalesPerson($dealerId, $dealerLocationId, $salesType, $salesPersonId, $salesPeople = array());

    /**
     * Find Sales People By Dealer ID
     * 
     * @param type $dealerId
     */
    public function findSalesPeople($dealerId);

    /**
     * Find Sales Person Type
     * 
     * @param string $leadType
     * @return string
     */
    public function findSalesType($leadType);

}
