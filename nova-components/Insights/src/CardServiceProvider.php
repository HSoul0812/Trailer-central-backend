<?php

declare(strict_types=1);

namespace TrailerTrader\Insights;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class CardServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            //Nova::script('insight-filters', __DIR__ . '/../dist/js/area-chart.js');
            Nova::script('insight-filters', __DIR__ . '/../dist/js/card.js');
            Nova::style('insight-filters', __DIR__ . '/../dist/css/card.css');
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Register the card's routes.
     */
    protected function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->prefix('nova-vendor/insight-filters')
            ->group(__DIR__ . '/../routes/api.php');
    }
}
