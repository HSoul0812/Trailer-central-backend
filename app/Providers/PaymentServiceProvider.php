<?php

namespace App\Providers;

use App\Services\Stripe\StripePaymentService;
use App\Services\Stripe\StripePaymentServiceInterface;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(StripePaymentServiceInterface::class, StripePaymentService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
