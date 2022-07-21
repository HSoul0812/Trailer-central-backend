<?php

namespace App\Repositories\Subscription;

use App\Repositories\Repository;

/**
 * Interface SubscriptionRepositoryInterface
 * @package App\Repositories\Subscription
 */
interface SubscriptionRepositoryInterface extends Repository {

    /**
     * Retrieves a customer from a given dealer id
     * @param $dealerId
     * @return object
     */
    public function getCustomerByDealerId($dealerId): object;

    /**
     * Retrieve all plans
     *
     * @return array
     */
    public function getExistingPlans(): array;

    /**
     * Subscribe to a selected plan
     * @param $dealerId
     * @param $planId
     * @return bool
     */
    public function subscribeToPlanByDealerId($dealerId, $planId): bool;

    /**
     * Updates a customer card
     * @param $dealerId
     * @param $token
     * @return bool
     */
    public function updateCardByDealerId($dealerId, $token): bool;
}
