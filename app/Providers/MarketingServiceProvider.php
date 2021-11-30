<?php

namespace App\Providers;

use App\Repositories\Marketing\TunnelRedisRepository;
use App\Repositories\Marketing\TunnelRepositoryInterface;
use App\Repositories\Marketing\Facebook\MarketplaceRepository;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Repositories\Marketing\Facebook\FilterRepository;
use App\Repositories\Marketing\Facebook\FilterRepositoryInterface;
use App\Services\Marketing\Facebook\MarketplaceService;
use App\Services\Marketing\Facebook\MarketplaceServiceInterface;
use App\Services\Dispatch\Facebook\MarketplaceService as MarketplaceDispatchService;
use App\Services\Dispatch\Facebook\MarketplaceServiceInterface as MarketplaceDispatchServiceInterface;
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
        // Integration Services
        $this->app->bind(MarketplaceServiceInterface::class, MarketplaceService::class);

        // Integration Repositories
        $this->app->bind(MarketplaceRepositoryInterface::class, MarketplaceRepository::class);
        $this->app->bind(FilterRepositoryInterface::class, FilterRepository::class);


        // Dispatch (Redis) Repositories
        $this->app->bind(TunnelRedisRepository::class, TunnelRepositoryInterface::class);

        // Dispatch Services
        $this->app->bind(MarketplaceDispatchServiceInterface::class, MarketplaceDispatchService::class);
    }

}