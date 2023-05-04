<?php

namespace App\Providers;

use App\Repositories\Payment\PaymentLogRepository;
use App\Repositories\Payment\PaymentLogRepositoryInterface;
use App\Services\Stripe\StripePaymentService;
use App\Services\Stripe\StripePaymentServiceInterface;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->bind(StripePaymentServiceInterface::class, StripePaymentService::class);
        $this->app->bind(PaymentLogRepositoryInterface::class, PaymentLogRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
    }
}
