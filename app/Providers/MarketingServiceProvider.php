<?php

namespace App\Providers;

use App\Repositories\Website\Parts\MarketplaceRepository;
use App\Repositories\Website\Parts\MarketplaceRepositoryInterface;
use App\Repositories\Website\Parts\FilterRepository;
use App\Repositories\Website\Parts\FilterRepositoryInterface;
use App\Services\Marketing\Facebook\MarketplaceService;
use App\Services\Marketing\Facebook\MarketplaceServiceInterface;
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
    }

}