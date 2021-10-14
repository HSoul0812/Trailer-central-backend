<?php

namespace App\Providers;

use App\Events\Ecommerce\QtyUpdated;
use App\Events\Parts\PartQtyUpdated;
use App\Listeners\Ecommerce\PartQtyReducer;
use App\Listeners\Parts\PartQtyAuditLogNotification;
use App\Listeners\Parts\PartReindexNotification;
use App\Repositories\Bulk\Parts\BulkDownloadRepositoryInterface;
use App\Repositories\Bulk\Parts\BulkReportRepository;
use App\Repositories\Bulk\Parts\BulkReportRepositoryInterface;
use App\Repositories\Bulk\Parts\BulkUploadRepositoryInterface;
use App\Repositories\Bulk\Parts\BulkDownloadRepository;
use App\Repositories\Bulk\Parts\BulkUploadRepository;
use App\Repositories\Parts\CostHistoryRepository;
use App\Repositories\Parts\CostHistoryRepositoryInterface;
use App\Services\Dms\ServiceOrder\BulkCsvTechnicianReportServiceInterface;
use App\Services\Dms\ServiceOrder\BulkCsvTechnicianReportService;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Repositories\Parts\PartRepository;
use App\Models\Parts\Textrail\Part as TextrailPart;
use App\Transformers\Parts\PartsTransformer;
use App\Transformers\Parts\PartsTransformerInterface;
use App\Transformers\Parts\Textrail\PartsTransformer as TextrailPartsTransformer;
use App\Models\Parts\Part;
use App\Repositories\Parts\Textrail\PartRepository as TextrailPartRepository;
use App\Http\Controllers\v1\Parts\Textrail\PartsController as TextrailPartsController;
use App\Http\Controllers\v1\Parts\PartsController;
use App\Repositories\Parts\AuditLogRepository;
use App\Repositories\Parts\AuditLogRepositoryInterface;
use App\Services\Export\Parts\BulkCsvDownloadJobService;
use App\Services\Export\Parts\BulkDownloadMonitoredJobServiceInterface;
use App\Services\Export\Parts\BulkReportJobService;
use App\Services\Export\Parts\BulkReportJobServiceInterface;
use App\Services\Parts\PartService;
use App\Services\Parts\PartServiceInterface;
use App\Services\Parts\Textrail\TextrailPartService;
use App\Services\Parts\Textrail\TextrailPartServiceInterface;
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
        QtyUpdated::class => [
            PartQtyReducer::class,
        ]
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
        $this->app->when(TextrailPartsController::class)
            ->needs(PartRepositoryInterface::class)
            ->give(function () {
                return new TextrailPartRepository(new TextrailPart());
            }); 
                        
        $this->app->when(TextrailPartsController::class)
            ->needs(PartsTransformerInterface::class)
            ->give(function () {
                return new TextrailPartsTransformer;
            }); 
        
        $this->app->bind(PartRepositoryInterface::class, PartRepository::class);  
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
        $this->app->bind(TextrailPartServiceInterface::class, TextrailPartService::class);
        $this->app->bind('App\Repositories\Parts\Textrail\BrandRepositoryInterface', 'App\Repositories\Parts\Textrail\BrandRepository');
        $this->app->bind('App\Repositories\Parts\Textrail\CategoryRepositoryInterface', 'App\Repositories\Parts\Textrail\CategoryRepository');
        $this->app->bind('App\Repositories\Parts\Textrail\ManufacturerRepositoryInterface', 'App\Repositories\Parts\Textrail\ManufacturerRepository');
        $this->app->bind('App\Repositories\Parts\Textrail\TypeRepositoryInterface', 'App\Repositories\Parts\Textrail\TypeRepository');
        $this->app->bind('App\Repositories\Parts\Textrail\ImageRepositoryInterface', 'App\Repositories\Parts\Textrail\ImageRepository');
        $this->app->bind(AuditLogRepositoryInterface::class, AuditLogRepository::class);
        $this->app->bind(CostHistoryRepositoryInterface::class, CostHistoryRepository::class);
        
        // CSV exporter bindings
        $this->app->bind(BulkDownloadRepositoryInterface::class, BulkDownloadRepository::class);
        $this->app->bind(BulkUploadRepositoryInterface::class, BulkUploadRepository::class);
        $this->app->bind(BulkDownloadMonitoredJobServiceInterface::class, BulkCsvDownloadJobService::class);
        $this->app->bind(BulkCsvTechnicianReportServiceInterface::class, BulkCsvTechnicianReportService::class);

        // PDF exporter bindings
        $this->app->bind(BulkReportRepositoryInterface::class, BulkReportRepository::class);
        $this->app->bind(BulkReportJobServiceInterface::class, BulkReportJobService::class);
    }
}
