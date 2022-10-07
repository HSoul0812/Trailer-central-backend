<?php

namespace App\Http\Controllers\v1\Stripe;

use App\Services\Stripe\StripePaymentServiceInterface;

class StripeController
{
    public function __construct(private StripePaymentServiceInterface $service) {

    }

    public function webhook() {

    }
}
