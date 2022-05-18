<?php

namespace App\Repositories\Subscription;

use Dingo\Api\Http\Request;
use App\Repositories\Repository;

interface SubscriptionRepositoryInterface extends Repository {

    /**
     * Retrieves a list of subscriptions
     *
     * @param $params
     * @return mixed
     */
    public function getAll($params);

    /**
     * Retrieves a customer
     *
     * @return mixed
     */
    public function getCustomer(Request $request);
}
