<?php

namespace App\Providers;

use App\Repositories\User\GeoLocationRepository;
use App\Repositories\User\GeoLocationRepositoryInterface;
use App\Services\User\GeolocationService;
use App\Services\User\GeoLocationServiceInterface;
use Illuminate\Support\ServiceProvider;
use App\Repositories\User\DealerLocationRepository;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Repositories\User\NewDealerUserRepository;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Repositories\User\NewUserRepository;
use App\Repositories\User\NewUserRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\User\SettingsRepository;
use App\Repositories\User\SettingsRepositoryInterface;
use App\Services\User\DealerOptionsService;
use App\Services\User\DealerOptionsServiceInterface;
use App\Repositories\User\DealerUserRepositoryInterface;
use App\Repositories\User\DealerUserRepository;
use App\Repositories\User\DealerXmlExportRepositoryInterface;
use App\Repositories\User\DealerXmlExportRepository;
use App\Repositories\User\DealerPartRepository;
use App\Repositories\User\DealerPartRepositoryInterface;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Interaction Services
        $this->app->bind(DealerOptionsServiceInterface::class, DealerOptionsService::class);

        // Interaction Repositories
        $this->app->bind(DealerLocationRepositoryInterface::class, DealerLocationRepository::class);
        $this->app->bind(NewUserRepositoryInterface::class, NewUserRepository::class);
        $this->app->bind(NewDealerUserRepositoryInterface::class, NewDealerUserRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
        $this->app->bind(DealerUserRepositoryInterface::class, DealerUserRepository::class);
        $this->app->bind(DealerXmlExportRepositoryInterface::class, DealerXmlExportRepository::class);
        $this->app->bind(DealerPartRepositoryInterface::class, DealerPartRepository::class);
        $this->app->bind(GeoLocationRepositoryInterface::class, GeoLocationRepository::class);
        $this->app->bind(GeoLocationServiceInterface::class, GeolocationService::class);
    }
}
