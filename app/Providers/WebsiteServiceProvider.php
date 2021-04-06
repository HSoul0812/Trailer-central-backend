<?php

namespace App\Providers;

use App\Repositories\CRM\Leads\TrackingRepository;
use App\Repositories\CRM\Leads\TrackingRepositoryInterface;
use App\Repositories\CRM\Leads\TrackingUnitRepository;
use App\Repositories\CRM\Leads\TrackingUnitRepositoryInterface;
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
