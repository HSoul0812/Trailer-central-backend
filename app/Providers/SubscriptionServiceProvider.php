<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Subscription\SubscriptionRepository;
use App\Repositories\Subscription\SubscriptionRepositoryInterface;

/**
 * Class SubscriptionServiceProvider
 * @package App\Providers
 */
class SubscriptionServiceProvider extends ServiceProvider
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
    public function boot()
    {
        //
    }

    /**
     * @return void
     */
    public function register()
    {
        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
    }
}
