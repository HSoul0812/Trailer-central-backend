<?php

namespace App\Services\Subscription;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Dingo\Api\Http\Request;

use App\Models\User\User;
use Stripe\StripeClient;

/**
 * Class StripeService
 * @package App\Services\Subscription
 */
class StripeService implements StripeServiceInterface
{
    /**
     * @var StripeClient $stripe
     */
    private $stripe;

    /**
     * Create a new StripeClient instance.
     */
    public function __construct() {
        $this->stripe = new StripeClient(config('services.stripe.secret_key'));
    }

    /**
     * @inheritDoc
     */
    public function getCustomerByDealerId($dealerId, int $transactions_limit = 0): object
    {
        $user = User::find($dealerId);
        $customer = $user->createOrGetStripeCustomer();

        if ($user->defaultPaymentMethod()) {
            // Sometimes the card comes nested and sometimes it doesn't
            $customer["card"] = $user->defaultPaymentMethod()->card ?? $user->defaultPaymentMethod();
        }

        $per_page = $request->transactions_limit ?? 0;
        $transactions = $this->getTransactionsByDealerId($dealerId, $per_page);
        $customer["transactions"] = $transactions["data"];

        return $customer;
    }

    /**
     * @inheritDoc
     */
    public function getSubscriptionsByDealerId($dealerId): object
    {
        $user = User::find($dealerId);
        $customer = $user->createOrGetStripeCustomer();

        return $customer->subscriptions;
    }

    /**
     * @inheritDoc
     */
    public function getTransactionsByDealerId($dealerId, $per_page): object
    {
        $user = User::find($dealerId);
        $customer = $user->createOrGetStripeCustomer();

        $params = [
            'customer' => $customer->id
        ];

        if ($per_page) {
            $params['limit'] = $per_page;
        }

        return $this->stripe->paymentIntents->all($params);
    }

    /**
     * @inheritDoc
     */
    public function getExistingPlans(): array
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

    /**
     * @inheritDoc
     */
    public function subscribeToPlanByDealerId($dealerId, $planId)
    {
        $user = User::find($dealerId);
        $customer = $user->createOrGetStripeCustomer();

        $paymentMethod = $user->defaultPaymentMethod();

        if ($paymentMethod) {
            return $user->newSubscription('default', $planId)
                ->create($paymentMethod->id, [
                    'email' => $customer->email,
                ]);
        } else {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function updateCardByDealerId($dealerId, $token): object
    {
        $user = User::find($dealerId);
        $customer = $user->createOrGetStripeCustomer();

        $paymentMethod = $this->stripe->customers->createSource(
            $customer->id,
            ['source' => $token]
        );

        return $user->updateDefaultPaymentMethod($paymentMethod->id);
    }
}
