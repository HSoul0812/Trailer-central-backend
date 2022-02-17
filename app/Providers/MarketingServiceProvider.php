<?php

namespace App\Providers;

use App\Repositories\Marketing\Craigslist\ActivePostRepository;
use App\Repositories\Marketing\Craigslist\ActivePostRepositoryInterface;
use App\Repositories\Marketing\Craigslist\InventoryRepository;
use App\Repositories\Marketing\Craigslist\InventoryRepositoryInterface;
use App\Repositories\Marketing\Craigslist\SchedulerRepository;
use App\Repositories\Marketing\Craigslist\SchedulerRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class MarketingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Marketing Repositories
        $this->app->bind(ActivePostRepositoryInterface::class, ActivePostRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(SchedulerRepositoryInterface::class, SchedulerRepository::class);
    }

}
