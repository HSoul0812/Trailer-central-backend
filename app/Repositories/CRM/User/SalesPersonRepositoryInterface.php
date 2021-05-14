<?php

namespace App\Repositories\CRM\User;

use App\Models\CRM\User\SalesPerson;
use App\Repositories\Repository;
use App\Utilities\JsonApi\RequestQueryable;

interface SalesPersonRepositoryInterface extends Repository, RequestQueryable {
    /**
     * Get By Smtp Email
     *
     * @param int $userId
     * @param string $email
     * @return null|SalesPerson
     */
    public function getBySmtpEmail(int $userId, string $email): ?SalesPerson;

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
     * Find Sales People By Dealer ID
     * 
     * @param type $dealerId
     * @param null|int $dealerLocationId
     * @param null|string $salesType
     */
    public function getSalesPeopleBy($dealerId, $dealerLocationId = null, $salesType = null);

    /**
     * Find Sales Person Type
     * 
     * @param string $leadType
     * @return string
     */
    public function findSalesType($leadType);

}
