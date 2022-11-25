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
use Stripe\Webhook;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StripePaymentService implements StripePaymentServiceInterface
{
    const STRIPE_SUCCESS_URL = '/success';
    const STRIPE_FAILURE_URL = '/cancel';
    const CHECKOUT_SESSION_COMPLETED_EVENT = 'checkout.session.completed';

    const PRICES = [
        'tt30' => 75.00,
        'tt60' => 100.00,
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

    public function createCheckoutSession(string $priceItem, array $metadata = []): string
    {
        $siteUrl = config('app.site_url');

        /** @var TcApiResponseInventory $inventory */
        $inventory = $metadata['inventory'];

        $planPrice = self::PRICES[$priceItem];

        $product = \Stripe\Product::create([
            'name' => $inventory->inventory_title
        ]);

        $priceObj = \Stripe\Price::create([
            "billing_scheme" => "per_unit",
            "currency" => "usd",
            "product" => $product->id,
            "unit_amount" => $this->numberToStripeFormat($planPrice),
        ]);

        $priceObjects[] = [
            'price' => $priceObj->id,
            'quantity' => 1,
        ];

        $checkout_session = Session::create([
            'line_items' => $priceObjects,
            'client_reference_id' => 'tt' . Str::uuid(),
            'metadata' => $metadata,
            'mode' => 'payment',
            'success_url' => $siteUrl . self::STRIPE_SUCCESS_URL,
            'cancel_url' => $siteUrl . self::STRIPE_FAILURE_URL,
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

    private function completeOrder(Session $session): int
    {
        try {
            DB::beginTransaction();

            $inventoryId = $session->metadata->inventory_id;
            $userId = $session->metadata->user_id;

            $this->paymentLogRepository->create([
                'payment_id' => $session->id,
                'client_reference_id' => $session->client_reference_id,
                'full_response' => json_encode($session->values())
            ]);

            $inventory = $this->inventoryService->show((int)$inventoryId);
            $inventoryExpiry =
                $inventory->tt_payment_expiration_date
                    ? Carbon::parse($inventory->tt_payment_expiration_date)
                    : Carbon::now()->startOfDay();

            if ($inventoryExpiry->startOfDay()->isBefore(Carbon::now())) {
                $inventoryExpiry = Carbon::now()->startOfDay();
            }
            // TODO: Extend expiry based on plan
            $inventoryExpiry = $inventoryExpiry->addMonth();
            $this->inventoryService->update($userId, [
                'inventory_id' => $inventoryId,
                'show_on_website' => 1,
                'tt_payment_expiration_date' => $inventoryExpiry
            ]);

            DB::commit();

            \Log::info('session', $session->values());
            \Log::info('inventory_id: ' . $inventoryId);

            return 200;
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::critical('Failed fulfilling order: ' . $e->getMessage());
            return 500;
        }
    }
}
