<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Class InventoryServiceProvider
 * @package App\Providers
 */
class InventoryServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Services\Import\Inventory\CsvImportServiceInterface', 'App\Services\Import\Inventory\CsvImportService');
    }
}
