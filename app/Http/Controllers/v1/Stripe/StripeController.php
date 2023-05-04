<?php

namespace App\Http\Controllers\v1\Stripe;

use App\Http\Controllers\Controller;
use App\Services\Stripe\StripePaymentServiceInterface;

class StripeController extends Controller
{
    public function __construct(private StripePaymentServiceInterface $service)
    {
    }

    public function webhook()
    {
        return response()->noContent($this->service->handleEvent());
    }

    public function plans()
    {
        return response()->json($this->service->paymentPlans());
    }
}
