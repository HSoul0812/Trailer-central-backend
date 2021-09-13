<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\LoggerService;
use App\Services\LoggerServiceInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoggerServiceInterface::class, LoggerService::class);
    }
}
