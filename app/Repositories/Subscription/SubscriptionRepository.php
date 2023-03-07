<?php

namespace App\Repositories\Subscription;

use App\Services\Subscription\StripeServiceInterface;
use Dingo\Api\Http\Request;

/**
 * Class SubscriptionRepository
 * @oackage App\Repositories\Subscription;
 */
class SubscriptionRepository implements SubscriptionRepositoryInterface {

    /**
     * @var StripeServiceInterface $service
     */
    private $service;

    /**
     * Create a new StripeServiceInterface instance.
     * @param StripeServiceInterface $stripeService
     */
    public function __construct(StripeServiceInterface $stripeService)
    {
        $this->service = $stripeService;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerByDealerId($dealerId): object {
        return $this->service->getCustomerByDealerId($dealerId);
    }

    /**
     * @inheritDoc
     */
    public function getExistingPlans(): array
    {
        return $this->service->getExistingPlans();
    }

    /**
     * @inheritDoc
     */
    public function subscribeToPlanByDealerId($dealerId, $planId): object
    {
        return $this->service->subscribeToPlanByDealerId($dealerId, $planId);
    }

    /**
     * @inheritDoc
     */
    public function updateCardByDealerId($dealerId, $token): object
    {
        return $this->service->updateCardByDealerId($dealerId, $token);
    }

    /**
     * @inheritDoc
     */
    public function getAll($params)
    {
        // TODO: Implement getAll() method.
    }

    /**
     * @inheritDoc
     */
    public function create($params)
    {
        // TODO: Implement create() method.
    }

    /**
     * @inheritDoc
     */
    public function update($params)
    {
        // TODO: Implement update() method.
    }

    /**
     * @inheritDoc
     */
    public function get($params)
    {
        // TODO: Implement get() method.
    }

    /**
     * @inheritDoc
     */
    public function delete($params)
    {
        // TODO: Implement delete() method.
    }
}
