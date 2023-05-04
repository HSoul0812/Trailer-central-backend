<?php

namespace App\Services\Stripe;

interface StripePaymentServiceInterface
{
    public function createCheckoutSession(string $planId, array $metadata = []): string;

    public function paymentPlans(): array;

    public function handleEvent(): int;
}
