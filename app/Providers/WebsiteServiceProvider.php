<?php

namespace App\Providers;

use App\Repositories\Website\Tracking\TrackingRepository;
use App\Repositories\Website\Tracking\TrackingRepositoryInterface;
use App\Repositories\Website\Tracking\TrackingUnitRepository;
use App\Repositories\Website\Tracking\TrackingUnitRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class WebsiteServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Repositories
        $this->app->bind(TrackingRepositoryInterface::class, TrackingRepository::class);
        $this->app->bind(TrackingUnitRepositoryInterface::class, TrackingUnitRepository::class);
    }

}
