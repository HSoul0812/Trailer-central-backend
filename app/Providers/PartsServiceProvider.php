<?php


namespace App\Providers;


use App\Events\Parts\PartUpdated;
use App\Listeners\Parts\PartQtyAuditLogNotification;
use App\Repositories\Bulk\BulkDownloadRepositoryInterface;
use App\Repositories\Bulk\Parts\BulkDownloadRepository;
use App\Repositories\Parts\AuditLogRepository;
use App\Repositories\Parts\AuditLogRepositoryInterface;
use App\Services\Export\Parts\CsvExportService;
use App\Services\Export\Parts\CsvExportServiceInterface;
use App\Services\Parts\PartService;
use App\Services\Parts\PartServiceInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class PartsServiceProvider extends ServiceProvider
{

    /**
     * events and listeners for parts
     */
    protected $listen = [
        // on part update
        PartUpdated::class => [

            // parts qty in bins updated
            PartQtyAuditLogNotification::class,
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
        $this->app->bind('App\Services\Import\Parts\CsvImportServiceInterface', 'App\Services\Import\Parts\CsvImportService');
        $this->app->bind(PartServiceInterface::class, PartService::class);
        $this->app->bind(AuditLogRepositoryInterface::class, AuditLogRepository::class);

        // CSV exporter bindings
        $this->app->bind(BulkDownloadRepositoryInterface::class, BulkDownloadRepository::class);
        $this->app->bind(CsvExportServiceInterface::class, CsvExportService::class);
        $this->app->when(CsvExportService::class)
            ->needs(Filesystem::class)
            ->give(function () { return Storage::disk('partsCsvExport');});
        $this->app->when(CsvExportService::class)
            ->needs(Filesystem::class)
            ->give(function () { return Storage::disk('partsCsvExport');});

    }
}
