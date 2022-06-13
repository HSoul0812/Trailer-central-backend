<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{

    public function boot()
    {
        //
    }

    public function register()
    {
        $this->app->bind('App\Services\Import\Inventory\CsvImportServiceInterface', 'App\Services\Import\Inventory\CsvImportService');
    }
}
