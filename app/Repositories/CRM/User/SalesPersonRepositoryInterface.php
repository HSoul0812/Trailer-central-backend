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

}
