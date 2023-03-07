<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\Subscription\StripeService;
use App\Services\Subscription\StripeServiceInterface;

/**
 * Class SubscriptionServiceProvider
 * @package App\Providers
 */
class StripeServiceProvider extends ServiceProvider
{

    /**
     * @var array
     */
    protected $listen = [
        //
    ];

    /**
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(StripeServiceInterface::class, StripeService::class);
    }
}
