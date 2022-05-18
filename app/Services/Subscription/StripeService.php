<?php

namespace App\Services\Subscription;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Dingo\Api\Http\Request;

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

    /**
     * @var $user
     */
    private $user;

    public function __construct($user) {
        $this->user = $user;
        $this->customer = $user->createOrGetStripeCustomer();
        $this->stripe = new StripeClient(env('STRIPE_SECRET'));
    }

    /**
     * Retrieves a customer with subscriptions and card information
     *
     * @param Request $request
     * @return object
     */
    public function getCustomer(Request $request): object
    {
        $customer = $this->customer;
        $transactions = isset($request->transactions_limit) ? $this->getTransactions($request->transactions_limit) : $this->getTransactions();
        $customer["transactions"] = $transactions["data"];

        return $this->customer;
    }

    /**
     * Retrieves all subscriptions from a given user
     *
     * @return object
     */
    public function getSubscriptions(): object
    {
        return $this->customer->subscriptions;
    }

    /**
     * Retrieves all the customer transactions
     *
     * @param $per_page
     * @return object
     */
    public function getTransactions($per_page = null): object
    {
        $params = [
            'customer' => $this->customer->id
        ];

        if ($per_page) {
            $params['limit'] = $per_page;
        }

        return $this->stripe->paymentIntents->all($params);
    }

    /**
     * Retrieves all existing plans
     *
     * @return array
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

    /**
     * Subscribe to a selected plan
     *
     * @param Request $request
     * @return array
     */
    public function subscribe(Request $request): array
    {
        try {
            if ($this->user->hasPaymentMethod()) {
                $paymentMethod = $this->user->defaultPaymentMethod();

                $this->user
                    ->newSubscription('default', $request->plan)
                    ->create($paymentMethod->id, [
                        'email' => $this->customer->email,
                    ]);
            } else {
                return [
                    'response' => [
                        'status' => 'error',
                        'message' => 'No payment method for this customer.'
                    ]
                ];
            }

            return [
                'response' => [
                    'status' => 'success',
                    'message' => 'Customer subscription successfully.'
                ]
            ];
        } catch (Exception $e) {
            return [
                'response' => [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Updates a customer card
     *
     * @param Request $request
     * @return array
     */
    public function updateCard(Request $request): array
    {
        try {
            $paymentMethod = $this->stripe->customers->createSource(
                $this->customer->id,
                ['source' => $request->token]
            );

            $this->user->updateDefaultPaymentMethod($paymentMethod->id);

            return [
                'response' => [
                    'status' => 'success',
                    'message' => 'Customer card updated successfully.'
                ]
            ];
        } catch (Exception $e) {
            return [
                'response' => [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]
            ];
        }
    }
}
