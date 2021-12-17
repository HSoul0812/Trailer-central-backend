<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\LoggerService;
use App\Services\LoggerServiceInterface;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom([
            __DIR__ . '/../../database/migrations/inventory',
            __DIR__ . '/../../database/migrations/leads',
        ]);

        ParallelTesting::setUpTestDatabase(function () {
            // needed to be able having the database schema and seed before every test
            Artisan::call('migrate');
            Artisan::call('db:seed');
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoggerServiceInterface::class, LoggerService::class);
    }
}
