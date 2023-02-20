<?php

namespace App\Services\Stripe;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

interface StripePaymentServiceInterface
{
    public function createCheckoutSession(string $planId, array $metadata = []): string;
    public function paymentPlans():array;
    public function handleEvent(): int;
}
