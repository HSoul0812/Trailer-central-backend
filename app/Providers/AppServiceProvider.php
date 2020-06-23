<?php

namespace App\Providers;

use App\Repositories\Bulk\BulkDownloadRepositoryInterface;
use App\Repositories\Bulk\Parts\BulkDownloadRepository;
use App\Repositories\Inventory\InventoryRepository;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Website\WebsiteRepository;
use App\Repositories\Website\WebsiteRepositoryInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use App\Services\Export\Parts\CsvExportService;
use App\Services\Export\Parts\CsvExportServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Showroom\ShowroomRepositoryInterface;
use App\Repositories\Showroom\ShowroomRepository;
use App\Repositories\Website\PaymentCalculator\SettingsRepositoryInterface;
use App\Repositories\Website\PaymentCalculator\SettingsRepository;
use App\Repositories\CRM\Leads\LeadRepository;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\Website\RedirectRepository;
use App\Repositories\Website\RedirectRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepository;
use App\Repositories\Website\EntityRepository;
use App\Repositories\Website\EntityRepositoryInterface;
use App\Repositories\Parts\CostModifierRepository;
use App\Repositories\Parts\CostModifierRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\CRM\User\SalesPersonRepository;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\User\DealerLocationRepository;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Repositories\CRM\Interactions\InteractionsRepository;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Validator::extend('bin_exists', 'App\Rules\Parts\BinExists@passes');
        \Validator::extend('type_exists', 'App\Rules\Parts\TypeExists@passes');
        \Validator::extend('category_exists', 'App\Rules\Parts\CategoryExists@passes');
        \Validator::extend('brand_exists', 'App\Rules\Parts\BrandExists@passes');
        \Validator::extend('part_exists', 'App\Rules\Parts\PartExists@passes');
        \Validator::extend('cycle_count_exists', 'App\Rules\Parts\CycleCountExists@passes');
        \Validator::extend('manufacturer_exists', 'App\Rules\Parts\ManufacturerExists@passes');
        \Validator::extend('price_format', 'App\Rules\PriceFormat@passes');
        \Validator::extend('lead_type_valid', 'App\Rules\CRM\Leads\ValidLeadType@passes');
        \Validator::extend('lead_status_valid', 'App\Rules\CRM\Leads\ValidLeadStatus@passes');
        \Validator::extend('sales_person_valid', 'App\Rules\CRM\User\ValidSalesPerson@passes');
        
        Builder::macro('whereLike', function($attributes, string $searchTerm) {
            foreach(array_wrap($attributes) as $attribute) {
               $this->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
            }

            return $this;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
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
        $this->app->bind('App\Repositories\Website\Parts\FilterRepositoryInterface', 'App\Repositories\Website\Parts\FilterRepository');
        $this->app->bind('App\Repositories\Website\Blog\PostRepositoryInterface', 'App\Repositories\Website\Blog\PostRepository');
        $this->app->bind('App\Services\Import\Parts\CsvImportServiceInterface', 'App\Services\Import\Parts\CsvImportService');
        $this->app->bind('App\Repositories\Bulk\BulkUploadRepositoryInterface', 'App\Repositories\Bulk\Parts\BulkUploadRepository');
        $this->app->bind('App\Repositories\Inventory\Floorplan\PaymentRepositoryInterface', 'App\Repositories\Inventory\Floorplan\PaymentRepository');
        $this->app->bind('App\Repositories\Dms\QuoteRepositoryInterface', 'App\Repositories\Dms\QuoteRepository');
        $this->app->bind(ShowroomRepositoryInterface::class, ShowroomRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
        $this->app->bind(LeadRepositoryInterface::class, LeadRepository::class);
        $this->app->bind(RedirectRepositoryInterface::class, RedirectRepository::class);        
        $this->app->bind(WebsiteRepositoryInterface::class, WebsiteRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(WebsiteConfigRepositoryInterface::class, WebsiteConfigRepository::class);
        $this->app->bind(EntityRepositoryInterface::class, EntityRepository::class);
        $this->app->bind(CostModifierRepositoryInterface::class, CostModifierRepository::class); 
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(SalesPersonRepositoryInterface::class, SalesPersonRepository::class);
        $this->app->bind(DealerLocationRepositoryInterface::class, DealerLocationRepository::class);
        $this->app->bind(InteractionsRepositoryInterface::class, InteractionsRepository::class);
        
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
