<?php

namespace App\Services\Stripe;

use App\DTOs\Inventory\TcApiResponseInventory;
use App\Repositories\Payment\PaymentLogRepositoryInterface;
use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Carbon;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Stripe;
use Stripe\StripeObject;
use Stripe\Webhook;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StripePaymentService implements StripePaymentServiceInterface
{
    const STRIPE_SUCCESS_URL = '/trailers/{id}/?payment_status=success';
    const STRIPE_FAILURE_URL = '/trailers/{id}/?payment_status=failed';
    const CHECKOUT_SESSION_COMPLETED_EVENT = 'checkout.session.completed';

    const PLANS = [
        'tt1' => [
            'name' => 'TrailerTrader-1days',
            'price' => 75.00,
            'description' => '1 day plan for publishing your listing with id {id} on TrailerTrader.com by user {user_id}',
            'duration' => 1,
        ],
        'tt30' => [
            'name' => 'TrailerTrader-30days',
            'price' => 75.00,
            'description' => '30 day plan for publishing your listing with id {id} on TrailerTrader.com by user {user_id}',
            'duration' => 30,
        ],
        'tt60' => [
            'name' => 'TrailerTrader-60days',
            'price' => 100.00,
            'description' => '60 days plan for publishing your listing with id {id} on TrailerTrader.com by user {user_id}',
            'duration' => 60,
        ],
    ];

    public function __construct(
        private PaymentLogRepositoryInterface $paymentLogRepository,
        private InventoryServiceInterface     $inventoryService
    )
    {
        Stripe::setApiKey(config('services.stripe.secret_key'));
    }

    /**
     * Parses a number, then returns the same number but without decimals separator, just like Stripe requires it
     * e.g. 123.44 -> 12344
     *      120 -> 12000
     *      120.0 -> 12000
     *      5.0 -> 500
     *
     * @param numeric $number
     * @return int
     */
    private function numberToStripeFormat($number): int
    {
        $numberWithTwoDecimals = number_format((float)$number, 2, '.', '');

        return (int)str_replace('.', '', $numberWithTwoDecimals);
    }

    public function createCheckoutSession(string $planId, array $metadata = []): string
    {
        $siteUrl = config('app.site_url');

        $inventoryId = $metadata['inventory_id'];
        $userId = $metadata['user_id'];

        $plan = self::PLANS[$planId];
        $planName = $plan['name'];
        $planDuration = $plan['duration'];
        $planDescription = $plan['description'];
        $planDescription = str_replace('{id}', $inventoryId, $planDescription);
        $planDescription = str_replace('{user_id}', $userId, $planDescription);

        $metadata['planKey'] = $planId;
        $metadata['planName'] = $planName;
        $metadata['planDescription'] = $planDescription;
        $metadata['planDuration'] = $planDuration;

        $product = $this->findOrCreatePlan($planId);

        $priceObjects[] = [
            'price' => $product->default_price,
            'quantity' => 1,
        ];

        $successUrl = str_replace('{id}', $inventoryId, self::STRIPE_SUCCESS_URL);
        $failUrl = str_replace('{id}', $inventoryId, self::STRIPE_FAILURE_URL);

        $checkout_session = Session::create([
            'line_items' => $priceObjects,
            'client_reference_id' => 'tt' . Str::uuid(),
            'metadata' => $metadata,
            'mode' => 'payment',
            'success_url' => $siteUrl .$successUrl,
            'cancel_url' => $siteUrl . $failUrl,
        ]);

        return $checkout_session->url;
    }

    public function handleEvent(): int
    {
        $endpointSecret = config('services.stripe.webhook_secret_key');
        $payload = @file_get_contents('php://input');
        $sigHeader = request()->server('HTTP_STRIPE_SIGNATURE');
        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            \Log::critical('Failed creating webhook: ' . $e->getMessage());
            return 400;
        }
        \Log::info('Event type: ' . $event->type);
        if ($event->type == self::CHECKOUT_SESSION_COMPLETED_EVENT) {
            $session = $event->data->object;
            return $this->completeOrder($session);
        }
        return 200;
    }

    private function findOrCreatePlan(string $planId): StripeObject {
        $plan = self::PLANS[$planId];
        $response = \Stripe\Product::search([
            'query' => "name:'{$plan['name']}' and active:'true'"
        ]);

        if(count($response->data) > 0) {
            return $response->data[0];
        }

        $product = \Stripe\Product::create([
            'name' => $planId,
            'description' => $plan['description'],
            'default_price_data' => [
                "currency" => "usd",
                "unit_amount" => $this->numberToStripeFormat($plan['price']),
            ]
        ]);

        return $product;
    }

    private function completeOrder(Session $session): int
    {
        try {
            DB::beginTransaction();

            $inventoryId = $session->metadata->inventory_id;
            $userId = $session->metadata->user_id;
            $planKey = $session->metadata->planKey;
            $planName = $session->metadata->planName;
            $planDescription = $session->metadata->planDescription;
            $planDuration = $session->metadata->planDuration;

            $this->paymentLogRepository->create([
                'payment_id' => $session->id,
                'client_reference_id' => $session->client_reference_id,
                'full_response' => json_encode($session->values()),
                'plan_key' => $planKey,
                'plan_name' => $planDescription,
                'plan_duration' => $planDuration
            ]);

            $inventory = $this->inventoryService->show((int)$inventoryId);
            $inventoryExpiry =
                $inventory->tt_payment_expiration_date
                    ? Carbon::parse($inventory->tt_payment_expiration_date)
                    : Carbon::now()->startOfDay();

            if ($inventoryExpiry->startOfDay()->isBefore(Carbon::now())) {
                $inventoryExpiry = Carbon::now()->startOfDay();
            }

            $planDuration = intval($planDuration) ?: 30;

            $inventoryExpiry = $inventoryExpiry
                ->addDays($planDuration)
                ->setTimezone('America/Indiana/Indianapolis')
                ->format('Y-m-d H:i:s');

            $this->inventoryService->update($userId, [
                'inventory_id' => $inventoryId,
                'show_on_website' => 1,
                'tt_payment_expiration_date' => $inventoryExpiry
            ]);

            DB::commit();

            return 200;
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::critical('Failed fulfilling order: ' . $e->getMessage());
            return 500;
        }
    }
}
