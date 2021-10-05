<?php

declare(strict_types=1);

namespace App\Providers;

use App\Nova\Dashboards\Inventory\PriceAverageByManufacturerInsights;
use App\Nova\Dashboards\Inventory\StockAverageByManufacturerInsights;
use App\Nova\Dashboards\Leads\LeadsAverageByManufacturerInsights;
use App\Repositories\Inventory\PriceAverageByManufacturerRepository;
use App\Repositories\Inventory\PriceAverageByManufacturerRepositoryInterface;
use App\Repositories\Inventory\StockAverageByManufacturerRepository;
use App\Repositories\Inventory\StockAverageByManufacturerRepositoryInterface;
use App\Repositories\Leads\LeadsAverageByManufacturerRepository;
use App\Repositories\Leads\LeadsAverageByManufacturerRepositoryInterface;
use App\Services\Inventory\PriceAverageByManufacturerService;
use App\Services\Inventory\PriceAverageByManufacturerServiceInterface;
use App\Services\Inventory\StockAverageByManufacturerService;
use App\Services\Inventory\StockAverageByManufacturerServiceInterface;
use App\Services\Leads\LeadsAverageByManufacturerService;
use App\Services\Leads\LeadsAverageByManufacturerServiceInterface;
use Illuminate\Support\Facades\Gate;
use JetBrains\PhpStorm\Pure;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Dashboard;
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

        $this->app->bind(PriceAverageByManufacturerRepositoryInterface::class, PriceAverageByManufacturerRepository::class);
        $this->app->bind(PriceAverageByManufacturerServiceInterface::class, PriceAverageByManufacturerService::class);

        $this->app->bind(LeadsAverageByManufacturerRepositoryInterface::class, LeadsAverageByManufacturerRepository::class);
        $this->app->bind(LeadsAverageByManufacturerServiceInterface::class, LeadsAverageByManufacturerService::class);
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
     * @return array<Dashboard>
     */
    protected function dashboards(): array
    {
        return [
            app()->make(StockAverageByManufacturerInsights::class),
            app()->make(PriceAverageByManufacturerInsights::class),
            app()->make(LeadsAverageByManufacturerInsights::class),
            // another dashboards
        ];
    }
}
