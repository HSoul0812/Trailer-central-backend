<?php

namespace App\Repositories\CRM\User;

use App\Models\CRM\User\SalesPerson;
use App\Models\User\NewDealerUser;
use App\Repositories\Repository;
use App\Utilities\JsonApi\RequestQueryable;
use Illuminate\Database\Eloquent\Collection;

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
     * @return null|SalesPerson
     */
    public function findNewestSalesPerson(
        int $dealerId,
        int $dealerLocationId,
        string $salesType
    ): ?SalesPerson;

    /**
     * Round Robin to Next Sales Person
     *
     * @param NewDealerUser $dealer
     * @param int $dealerLocationId
     * @param string $salesType
     * @param null|SalesPerson $newestSalesPerson
     * @return null|SalesPerson
     */
    public function roundRobinSalesPerson(
        NewDealerUser $dealer,
        int $dealerLocationId,
        string $salesType,
        ?SalesPerson $newestSalesPerson = null
    ): ?SalesPerson;

    /**
     * Find Sales People By Dealer ID
     *
     * @param int $dealerId
     * @param null|int $dealerLocationId
     * @param null|string $salesType
     * @return Collection<SalesPerson>
     */
    public function getSalesPeopleBy(
        int $dealerId,
        ?int $dealerLocationId = null,
        ?string $salesType = null
    ): Collection;

    /**
     * Find Sales Person Type
     * 
     * @param string $leadType
     * @return string
     */
    public function findSalesType(string $leadType): string;

    /**
     * Find Sales Person by Email
     *
     * @param int $userId
     * @param string $email
     * @return ?SalesPerson
     */
    public function getByEmail(int $userId, string $email): ?SalesPerson;

}
