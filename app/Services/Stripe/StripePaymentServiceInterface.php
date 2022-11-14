<?php

namespace App\Services\Stripe;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

interface StripePaymentServiceInterface
{
    public function createCheckoutSession(string $priceItem, array $metadata = []): Redirector|Application|RedirectResponse;

    public function handleEvent(): int;
}
