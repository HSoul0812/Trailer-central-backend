<?php

namespace App\Repositories\Subscription;

use App\Services\Subscription\StripeService;
use Dingo\Api\Http\Request;

use Illuminate\Support\Facades\Auth;

class SubscriptionRepository implements SubscriptionRepositoryInterface {

    /**
     * @var Auth $user
     */
    private $user;

    /**
     * @var StripeService $service
     */
    private $service;

    /**
     * Create a new SubscriptionRepository instance.
     *
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->service = new StripeService($this->user);
    }

    /**
     * Retrieves all subscriptions from a given user
     *
     * @param array $params
     * @return mixed
     */
    public function getAll($params) {
        return $this->service->getSubscriptions()->data;
    }

    /**
     * Retrieves a customer with subscriptions and card information
     *
     * @return mixed
     */
    public function getCustomer() {
        return $this->service->getCustomer();
    }

    /**
     * Subscribe to a selected plan
     *
     * @param Request $request
     * @return mixed
     */
    public function subscribe(Request $request)
    {
        return $this->service->subscribe($request);
    }

    /**
     * @param $params
     * @return void
     */
    public function create($params)
    {
        // TODO: Implement create() method.
    }

    /**
     * @param $params
     * @return void
     */
    public function update($params)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param $params
     * @return void
     */
    public function get($params)
    {
        // TODO: Implement get() method.
    }

    /**
     * @param $params
     * @return void
     */
    public function delete($params)
    {
        // TODO: Implement delete() method.
    }
}
