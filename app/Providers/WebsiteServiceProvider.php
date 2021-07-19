<?php

namespace App\Providers;

use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepository;
use App\Repositories\Website\Config\DefaultConfigRepositoryInterface;
use App\Repositories\Website\Config\DefaultConfigRepository;
use App\Repositories\Website\Tracking\TrackingRepository;
use App\Repositories\Website\Tracking\TrackingRepositoryInterface;
use App\Repositories\Website\Tracking\TrackingUnitRepository;
use App\Repositories\Website\Tracking\TrackingUnitRepositoryInterface;
use App\Repositories\Website\WebsiteDealerUrlRepository;
use App\Repositories\Website\WebsiteDealerUrlRepositoryInterface;
use App\Services\Website\WebsiteDealerUrlService;
use App\Services\Website\WebsiteDealerUrlServiceInterface;
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
        $this->app->bind(WebsiteConfigRepositoryInterface::class, WebsiteConfigRepository::class);
        $this->app->bind(DefaultConfigRepositoryInterface::class, DefaultConfigRepository::class);
        $this->app->bind(WebsiteDealerUrlRepositoryInterface::class, WebsiteDealerUrlRepository::class);

        // Services
        $this->app->bind(WebsiteDealerUrlServiceInterface::class, WebsiteDealerUrlService::class);
    }

}
