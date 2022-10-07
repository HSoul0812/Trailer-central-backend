<?php

namespace App\Services\Stripe;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Checkout\Session;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class StripePaymentService implements StripePaymentServiceInterface
{

    public function __construct() {
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
        \Log::info('session', $session->values());
        \Log::info($session->metadata->inventory_id);
    }
}
