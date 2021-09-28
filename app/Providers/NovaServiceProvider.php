<?php

declare(strict_types=1);

namespace App\Providers;

use App\Nova\Dashboards\Inventory\StockAverageByManufacturerInsights;
use App\Repositories\Inventory\StockAverageByManufacturerRepository;
use App\Repositories\Inventory\StockAverageByManufacturerRepositoryInterface;
use App\Services\Inventory\StockAverageByManufacturerService;
use App\Services\Inventory\StockAverageByManufacturerServiceInterface;
use Illuminate\Support\Facades\Gate;
use JetBrains\PhpStorm\Pure;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Element;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array<Element>
     */
    public function tools(): array
    {
        return [];
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(StockAverageByManufacturerRepositoryInterface::class, StockAverageByManufacturerRepository::class);
        $this->app->bind(StockAverageByManufacturerServiceInterface::class, StockAverageByManufacturerService::class);
    }

    /**
     * Register the Nova routes.
     */
    protected function routes(): void
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewNova', function ($user) {
            return in_array($user->email, [], true);
        });
    }

    /**
     * Get the cards that should be displayed on the default Nova dashboard.
     *
     * @return array<Element>
     */
    #[Pure]
    protected function cards(): array
    {
        return [
            new Help(),
        ];
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return array<Element>
     */
    protected function dashboards(): array
    {
        return [
            app()->make(StockAverageByManufacturerInsights::class),
            // another dashboards
        ];
    }
}
