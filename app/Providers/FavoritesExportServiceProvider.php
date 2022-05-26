<?php

namespace App\Providers;

use App\Repositories\Export\FavoritesRepository;
use App\Repositories\Export\FavoritesRepositoryInterface;
use App\Services\Export\Favorites\InventoryCsvExporter;
use App\Services\Export\Favorites\InventoryCsvExporterInterface;
use Illuminate\Support\ServiceProvider;

class FavoritesExportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(FavoritesRepositoryInterface::class, FavoritesRepository::class);
        $this->app->bind(InventoryCsvExporterInterface::class, InventoryCsvExporter::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
