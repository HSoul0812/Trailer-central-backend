<?php

namespace App\Providers;

use App\Events\Parts\PartQtyUpdated;
use App\Listeners\Parts\PartQtyAuditLogNotification;
use App\Listeners\Parts\PartReindexNotification;
use App\Repositories\Bulk\BulkDownloadRepositoryInterface;
use App\Repositories\Bulk\BulkUploadRepositoryInterface;
use App\Repositories\Bulk\Parts\BulkDownloadRepository;
use App\Repositories\Bulk\Parts\BulkUploadRepository;
use App\Repositories\Parts\AuditLogRepository;
use App\Repositories\Parts\AuditLogRepositoryInterface;
use App\Services\Export\Parts\BulkCsvDownloadJobService;
use App\Services\Export\Parts\BulkDownloadJobServiceInterface;
use App\Services\Export\Parts\BulkUploadJobService;
use App\Services\Export\Parts\BulkUploadJobServiceInterface;
use App\Services\Parts\PartService;
use App\Services\Parts\PartServiceInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class PartsServiceProvider extends ServiceProvider
{

    /**
     * events and listeners for parts
     */
    protected $listen = [
        // on part update
        PartQtyUpdated::class => [

            // parts qty in bins updated
            PartQtyAuditLogNotification::class,

            // part should be reindexed
            PartReindexNotification::class,
        ],
    ];

    public function boot()
    {
        // validation rules
        \Validator::extend('bin_exists', 'App\Rules\Parts\BinExists@passes');
        \Validator::extend('type_exists', 'App\Rules\Parts\TypeExists@passes');
        \Validator::extend('category_exists', 'App\Rules\Parts\CategoryExists@passes');
        \Validator::extend('brand_exists', 'App\Rules\Parts\BrandExists@passes');
        \Validator::extend('part_exists', 'App\Rules\Parts\PartExists@passes');
        \Validator::extend('cycle_count_exists', 'App\Rules\Parts\CycleCountExists@passes');
        \Validator::extend('manufacturer_exists', 'App\Rules\Parts\ManufacturerExists@passes');
        \Validator::extend('sku_type', 'App\Rules\Parts\SkuType@passes');

        // register events and listeners
        foreach ($this->listen as $event => $listeners) {
            foreach (array_unique($listeners) as $listener) {
                Event::listen($event, $listener);
            }
        }

    }

    public function register()
    {
        //
        $this->app->bind('App\Repositories\Parts\PartRepositoryInterface', 'App\Repositories\Parts\PartRepository');
        $this->app->bind('App\Repositories\Parts\BinRepositoryInterface', 'App\Repositories\Parts\BinRepository');
        $this->app->bind('App\Repositories\Parts\CycleCountRepositoryInterface', 'App\Repositories\Parts\CycleCountRepository');
        $this->app->bind('App\Repositories\Parts\BrandRepositoryInterface', 'App\Repositories\Parts\BrandRepository');
        $this->app->bind('App\Repositories\Parts\CategoryRepositoryInterface', 'App\Repositories\Parts\CategoryRepository');
        $this->app->bind('App\Repositories\Parts\ManufacturerRepositoryInterface', 'App\Repositories\Parts\ManufacturerRepository');
        $this->app->bind('App\Repositories\Parts\TypeRepositoryInterface', 'App\Repositories\Parts\TypeRepository');
        $this->app->bind('App\Repositories\Parts\VendorRepositoryInterface', 'App\Repositories\Parts\VendorRepository');
        $this->app->bind('App\Repositories\Parts\PartOrderRepositoryInterface', 'App\Repositories\Parts\PartOrderRepository');
        $this->app->bind('App\Services\Import\Parts\CsvImportServiceInterface', 'App\Services\Import\Parts\CsvImportService');
        $this->app->bind(PartServiceInterface::class, PartService::class);
        $this->app->bind(AuditLogRepositoryInterface::class, AuditLogRepository::class);

        // CSV exporter bindings
        $this->app->bind(BulkDownloadRepositoryInterface::class, BulkDownloadRepository::class);
        $this->app->bind(BulkUploadRepositoryInterface::class, BulkUploadRepository::class);
        $this->app->bind(BulkDownloadJobServiceInterface::class, BulkCsvDownloadJobService::class);
        $this->app->bind(BulkUploadJobServiceInterface::class, BulkUploadJobService::class);
    }
}
