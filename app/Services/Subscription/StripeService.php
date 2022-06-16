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

    public function __construct() {
        $this->stripe = new StripeClient(config('services.stripe.secret_key'));
    }

    /**
     * Retrieves a customer with subscriptions and card information
     *
     * @param Request $request
     * @return object
     */
    public function getCustomer(Request $request): object
    {
        $user = User::find($request->dealer_id);
        $customer = $user->createOrGetStripeCustomer();

        if ($user->defaultPaymentMethod()) {
            $customer["card"] = $user->defaultPaymentMethod()->card;
        }

        $per_page = $request->transactions_limit ?? 0;
        $transactions = $this->getTransactions($customer, $per_page);
        $customer["transactions"] = $transactions["data"];

        return $customer;
    }

    /**
     * Retrieves all subscriptions from a given user
     *
     * @param $request
     * @return object
     */
    public function getSubscriptions($request): object
    {
        $user = User::find($request->dealer_id);
        $customer = $user->createOrGetStripeCustomer();

        return $customer->subscriptions;
    }

    /**
     * Retrieves all the customer transactions
     *
     * @param $per_page
     * @return object
     */
    public function getTransactions($customer, $per_page): object
    {
        $params = [
            'customer' => $customer->id
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
        $user = User::find($request->dealer_id);
        $customer = $user->createOrGetStripeCustomer();

        try {
            if ($user->hasPaymentMethod()) {
                $paymentMethod = $user->defaultPaymentMethod();

                $user
                    ->newSubscription('default', $request->plan)
                    ->create($paymentMethod->id, [
                        'email' => $customer->email,
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
        $user = User::find($request->dealer_id);
        $customer = $user->createOrGetStripeCustomer();

        try {
            $paymentMethod = $this->stripe->customers->createSource(
                $customer->id,
                ['source' => $request->token]
            );

            $user->updateDefaultPaymentMethod($paymentMethod->id);

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
