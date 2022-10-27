<?php

namespace App\Services\Stripe;
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
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class StripePaymentService implements StripePaymentServiceInterface
{

    public function __construct(
        private PaymentLogRepositoryInterface $paymentLogRepository,
        private InventoryServiceInterface $inventoryService
    ) {
        Stripe::setApiKey(config('services.stripe.secret_key'));
    }

    public function createCheckoutSession(string $priceItem, array $metadata=[]): Redirector|Application|RedirectResponse
    {
        $siteUrl = config('app.site_url');
        $checkout_session = Session::create([
            'line_items' => [[
                'price' => "$priceItem",
                'quantity' => 1
            ]],
            'client_reference_id' => 'tt' . uuid_create(),
            'metadata' => $metadata,
            'mode' => 'payment',
            'success_url' => $siteUrl . '/success',
            'cancel_url' => $siteUrl . '/cancel',
        ]);
        return redirect($checkout_session->url);
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
        } catch(UnexpectedValueException|SignatureVerificationException $e) {
            return 400;
        }
        \Log::info('Event type: ' . $event->type);
        if($event->type == 'checkout.session.completed') {
            $session = $event->data->object;
            $this->fulfillOrder($session);
        }
        return 200;
    }

    private function fulfillOrder(Session $session) {
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

        if($inventoryExpiry->startOfDay()->isBefore(Carbon::now())) {
            $inventoryExpiry = Carbon::now()->startOfDay();
        }
        // TODO: Extend expiry based on plan
        $inventoryExpiry = $inventoryExpiry->addMonth();
        $this->inventoryService->update($userId, [
            'inventory_id' => $inventoryId,
            'show_on_website' => 1,
            'tt_payment_expiration_date' => $inventoryExpiry
        ]);

        \Log::info('session', $session->values());
        \Log::info('inventory_id: ' . $inventoryId);
    }
}
