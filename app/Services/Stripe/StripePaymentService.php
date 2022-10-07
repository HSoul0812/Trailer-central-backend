<?php

namespace App\Services\Stripe;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use \Stripe\Stripe;
use \Stripe\Checkout\Session;

class StripePaymentService implements StripePaymentServiceInterface
{

    public function __construct() {
        Stripe::setApiKey(config('services.stripe.secret_key'));
    }

    public function createCheckoutSession(string $priceItem): Redirector|Application|RedirectResponse
    {
        $siteUrl = config('app.site_url');
        $checkout_session = Session::create([
            'line_items' => [
                'price' => "$priceItem",
                'quantity' => 1,
            ],
            'mode' => 'payment',
            'success_url' => $siteUrl . '/success',
            'cancel_url' => $siteUrl . '/cancel'
        ]);
        return redirect($checkout_session->url);
    }

    public function handleEvent() {
        $payload = @file_get_contents('php://input');

    }

    public function fulfillOrder() {

    }
}
