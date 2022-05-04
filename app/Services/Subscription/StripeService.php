<?php

namespace App\Services\Subscription;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Models\User\User;
use Stripe\StripeClient;

class StripeService implements StripeServiceInterface
{
    /**
     * @var StripeClient $stripe
     */
    private $stripe;

    /**
     * @var $customer
     */
    private $customer;

    public function __construct($user) {
        $this->customer = $user->createOrGetStripeCustomer();
        $this->stripe = new StripeClient(env('STRIPE_SECRET'));
    }

    /**
     * Retrieves all subscriptions from a given user
     *
     */
    public function getCustomer() {
        return $this->customer;
    }

    /**
     * Retrieves a customer with subscriptions and card information
     *
     */
    public function getSubscriptions() {
        return $this->customer->subscriptions;
    }

    /**
     * Retrieves all the customer transactions
     *
     */
    public function getTransactions() {
        return $this->stripe->paymentIntents->all(
            ['customer' => $this->customer->id]
        );
    }

    /**
     * Retrieves all existing plans
     *
     */
    public function getPlans(): array
    {
        $plansRaw = $this->stripe->plans->all();
        $plans = $plansRaw->data;

        foreach($plans as $plan) {
            $prod = $this->stripe->products->retrieve(
                $plan->product,[]
            );
            $plan->product = $prod;
        }

        return $plans;
    }
}
