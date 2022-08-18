<?php

namespace App\Services\Subscription;

/**
 * interface StripeServiceInterface
 *
 */
interface StripeServiceInterface {

    /**
     * Retrieves a customer from a given dealer id
     * @param $dealerId
     * @param int $transactions_limit
     * @return object
     */
    public function getCustomerByDealerId($dealerId, int $transactions_limit = 0): object;

    /**
     * Retrieves all subscriptions from a given user
     *
     * @param $dealerId
     * @return object
     */
    public function getSubscriptionsByDealerId($dealerId): object;

    /**
     * Retrieves all the customer transactions
     *
     * @param $dealerId
     * @param $per_page
     * @return object
     */
    public function getTransactionsByDealerId($dealerId, $per_page): object;

    /**
     * Retrieves all existing plans
     *
     * @return array
     */
    public function getExistingPlans(): array;

    /**
     * Subscribe to a selected plan
     *
     * @param $dealerId
     * @param $planId
     * @return boolean|\Laravel\Cashier\Subscription
     */
    public function subscribeToPlanByDealerId($dealerId, $planId);

    /**
     * Updates a customer card
     *
     * @param $dealerId
     * @param $token
     */
    public function updateCardByDealerId($dealerId, $token): object;
}
