<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Http\Response\Header;
use App\Domains\Throttle\Handler;
use App\Repositories\WebsiteUser\UserTrackingRepository;
use App\Repositories\WebsiteUser\UserTrackingRepositoryInterface;
use App\Services\LoggerService;
use App\Services\LoggerServiceInterface;
use Cache;
use Closure;
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

        Cache::macro('rememberWithNewTTL', function ($key, $ttl, Closure $callback) {
            $value = $this->get($key);

            // If the item exists in the cache we will just return this immediately and if
            // not we will execute the given Closure and cache the result of that for a
            // given number of seconds so it's available for all subsequent requests.
            if (!is_null($value)) {
                $this->put($key, $value, value($ttl));

                return $value;
            }

            $this->put($key, $value = $callback(), value($ttl));

            return $value;
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoggerServiceInterface::class, LoggerService::class);

        $this->app->bind(UserTrackingRepositoryInterface::class, UserTrackingRepository::class);

        $this->app->singleton(Header::class, fn () => new Header());

        $this->app->alias('api.limiting', Handler::class);

        $this->app->singleton('api.limiting', function ($app) {
            return new Handler($app, $app['cache'], config('api.throttling'));
        });
    }
}
