<?php

namespace App\Providers;

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
    }

}
