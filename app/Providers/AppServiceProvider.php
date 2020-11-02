<?php

namespace App\Providers;

use App\Repositories\Inventory\FileRepository;
use App\Repositories\Inventory\FileRepositoryInterface;
use App\Repositories\Inventory\ImageRepository;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Repositories\Inventory\StatusRepository;
use App\Repositories\Inventory\StatusRepositoryInterface;
use App\Repositories\Website\DealerProxyRedisRepository;
use App\Repositories\Website\DealerProxyRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use App\Models\Feed\Mapping\Incoming\DealerIncomingMapping;
use App\Nova\Observer\DealerIncomingMappingObserver;
use App\Repositories\Bulk\BulkDownloadRepositoryInterface;
use App\Repositories\Bulk\Parts\BulkDownloadRepository;
use App\Repositories\Dms\FinancingCompanyRepository;
use App\Repositories\Dms\FinancingCompanyRepositoryInterface;
use App\Repositories\Inventory\CategoryRepository;
use App\Repositories\Inventory\CategoryRepositoryInterface;
use App\Repositories\Inventory\AttributeRepository;
use App\Repositories\Inventory\AttributeRepositoryInterface;
use App\Repositories\Dms\PurchaseOrder\PurchaseOrderReceiptRepository;
use App\Repositories\Dms\PurchaseOrder\PurchaseOrderReceiptRepositoryInterface;
use App\Repositories\Dms\ServiceOrderRepository;
use App\Repositories\Dms\ServiceOrderRepositoryInterface;
use App\Repositories\Dms\Quickbooks\AccountRepository;
use App\Repositories\Dms\Quickbooks\AccountRepositoryInterface;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepository;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepositoryInterface;
use App\Repositories\Pos\SaleRepository;
use App\Repositories\Pos\SaleRepositoryInterface;
use App\Repositories\Inventory\InventoryRepository;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Inventory\ManufacturerRepository;
use App\Repositories\Inventory\ManufacturerRepositoryInterface;
use App\Repositories\Website\TowingCapacity\MakesRepository;
use App\Repositories\Website\TowingCapacity\MakesRepositoryInterface;
use App\Repositories\Website\TowingCapacity\VehiclesRepository;
use App\Repositories\Website\TowingCapacity\VehiclesRepositoryInterface;
use App\Repositories\Website\WebsiteRepository;
use App\Repositories\Website\WebsiteRepositoryInterface;
use App\Repositories\Website\PaymentCalculator\SettingsRepositoryInterface;
use App\Repositories\Website\PaymentCalculator\SettingsRepository;
use App\Repositories\Website\RedirectRepository;
use App\Repositories\Website\RedirectRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepository;
use App\Repositories\Website\EntityRepository;
use App\Repositories\Website\EntityRepositoryInterface;
use App\Repositories\Website\Forms\FieldMapRepositoryInterface;
use App\Repositories\Website\Forms\FieldMapRepository;
use App\Repositories\Showroom\ShowroomRepositoryInterface;
use App\Repositories\Showroom\ShowroomRepository;
use App\Repositories\CRM\Invoice\InvoiceRepository;
use App\Repositories\CRM\Invoice\InvoiceRepositoryInterface;
use App\Repositories\CRM\Payment\PaymentRepository;
use App\Repositories\CRM\Payment\PaymentRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepository;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Interactions\InteractionsRepository;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepository;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\CRM\Text\BlastRepository;
use App\Repositories\CRM\Text\BlastRepositoryInterface;
use App\Repositories\CRM\Text\CampaignRepository;
use App\Repositories\CRM\Text\CampaignRepositoryInterface;
use App\Repositories\CRM\Text\TemplateRepository;
use App\Repositories\CRM\Text\TemplateRepositoryInterface;
use App\Repositories\CRM\Text\TextRepository;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\CRM\Text\NumberRepository;
use App\Repositories\CRM\Text\NumberRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepository;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Repositories\CRM\Customer\CustomerRepository;
use App\Repositories\Parts\CostModifierRepository;
use App\Repositories\Parts\CostModifierRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\User\DealerLocationRepository;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\Export\Parts\CsvExportService;
use App\Services\Export\Parts\CsvExportServiceInterface;
use App\Services\CRM\Text\TwilioService;
use App\Services\CRM\Text\TextServiceInterface;
use App\Services\CRM\Text\BlastService;
use App\Services\CRM\Text\BlastServiceInterface;
use App\Services\CRM\Text\CampaignService;
use App\Services\CRM\Text\CampaignServiceInterface;
use App\Services\CRM\Interactions\InteractionEmailService;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Services\Parts\PartServiceInterface;
use App\Services\Parts\PartService;
use App\Services\Website\Log\LogServiceInterface;
use App\Services\Website\Log\LogService;
use App\Services\CRM\Leads\AutoAssignService;
use App\Services\CRM\Leads\AutoAssignServiceInterface;
use App\Jobs\Mailer\UserMailerJob;
use App\Rules\CRM\Leads\ValidLeadSource;
use Laravel\Nova\Nova;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Validator::extend('price_format', 'App\Rules\PriceFormat@passes');
        \Validator::extend('dealer_location_valid', 'App\Rules\User\ValidDealerLocation@passes');
        \Validator::extend('website_valid', 'App\Rules\Website\ValidWebsite@passes');
        \Validator::extend('inventory_valid', 'App\Rules\Inventory\ValidInventory@passes');
        \Validator::extend('inventory_mfg_exists', 'App\Rules\Inventory\ManufacturerValid@passes');
        \Validator::extend('inventory_mfg_valid', 'App\Rules\Inventory\ManufacturerValid@passes');
        \Validator::extend('inventory_cat_exists', 'App\Rules\Inventory\CategoryExists@passes');
        \Validator::extend('inventory_cat_valid', 'App\Rules\Inventory\CategoryValid@passes');
        \Validator::extend('inventory_brand_exists', 'App\Rules\Inventory\BrandValid@passes');
        \Validator::extend('inventory_brand_valid', 'App\Rules\Inventory\BrandValid@passes');
        \Validator::extend('lead_exists', 'App\Rules\CRM\Leads\LeadExists@passes');
        \Validator::extend('lead_type_valid', 'App\Rules\CRM\Leads\ValidLeadType@passes');
        \Validator::extend('lead_status_valid', 'App\Rules\CRM\Leads\ValidLeadStatus@passes');
        \Validator::extend('lead_source_valid', 'App\Rules\CRM\Leads\ValidLeadSource@passes');
        \Validator::extend('sales_person_valid', 'App\Rules\CRM\User\ValidSalesPerson@passes');
        \Validator::extend('interaction_type_valid', 'App\Rules\CRM\Interactions\ValidInteractionType@passes');
        \Validator::extend('campaign_action_valid', 'App\Rules\CRM\Email\CampaignActionValid@passes');
        \Validator::extend('text_exists', 'App\Rules\CRM\Text\TextExists@passes');
        \Validator::extend('text_template_exists', 'App\Rules\CRM\Text\TemplateExists@passes');
        \Validator::extend('parts_sku_unique', 'App\Rules\Parts\SkuUnique@validate');
        \Validator::extend('vendor_exists', 'App\Rules\Inventory\VendorExists@passes');
        \Validator::extend('valid_form_map_type', 'App\Rules\Website\Forms\ValidMapType@passes');
        \Validator::extend('valid_form_map_field', 'App\Rules\Website\Forms\ValidMapField@passes');
        \Validator::extend('valid_form_map_table', 'App\Rules\Website\Forms\ValidMapTable@passes');
        \Validator::extend('valid_part_order_status', 'App\Rules\Parts\ValidOrderStatus@passes');
        \Validator::extend('valid_part_fulfillment', 'App\Rules\Parts\ValidFulfillment@passes');

        Builder::macro('whereLike', function($attributes, string $searchTerm) {
            foreach(array_wrap($attributes) as $attribute) {
               $this->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
            }

            return $this;
        });

        Nova::serving(function () {
            DealerIncomingMapping::observe(DealerIncomingMappingObserver::class);
        });

        // add other migration directories
        $this->loadMigrationsFrom([
            // old directory
            __DIR__ . '/../../database/migrations',

            // dms migrations
            __DIR__ . '/../../database/migrations/dms',

            // integrations migrations
            __DIR__ . '/../../database/migrations/integrations',

            // inventory migrations
            __DIR__ . '/../../database/migrations/inventory',

            // website migrations
            __DIR__ . '/../../database/migrations/website',

            // parts migrations
            __DIR__ . '/../../database/migrations/parts',

            // parts crm
            __DIR__ . '/../../database/migrations/crm',
            
            // dealer migrations
            __DIR__ . '/../../database/migrations/dealer',

            // add other migration directories here
            __DIR__ . '/../../database/migrations/utilities',
        ]);

        // log all queries
        if (env('APP_LOG_QUERIES')) {
            DB::listen(function($query) {
                Log::info(
                    $query->sql,
                    $query->bindings,
                    $query->time
                );
            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind('App\Repositories\Website\Parts\FilterRepositoryInterface', 'App\Repositories\Website\Parts\FilterRepository');
        $this->app->bind('App\Repositories\Website\Blog\PostRepositoryInterface', 'App\Repositories\Website\Blog\PostRepository');
        $this->app->bind(TextServiceInterface::class, TwilioService::class);
        $this->app->bind(BlastServiceInterface::class, BlastService::class);
        $this->app->bind(CampaignServiceInterface::class, CampaignService::class);
        $this->app->bind(InteractionEmailServiceInterface::class, InteractionEmailService::class);
        $this->app->bind('App\Repositories\Bulk\BulkUploadRepositoryInterface', 'App\Repositories\Bulk\Parts\BulkUploadRepository');
        $this->app->bind('App\Repositories\Inventory\Floorplan\PaymentRepositoryInterface', 'App\Repositories\Inventory\Floorplan\PaymentRepository');
        $this->app->bind(ShowroomRepositoryInterface::class, ShowroomRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
        $this->app->bind(LeadRepositoryInterface::class, LeadRepository::class);
        $this->app->bind(TextRepositoryInterface::class, TextRepository::class);
        $this->app->bind(TemplateRepositoryInterface::class, TemplateRepository::class);
        $this->app->bind(CampaignRepositoryInterface::class, CampaignRepository::class);
        $this->app->bind(BlastRepositoryInterface::class, BlastRepository::class);
        $this->app->bind(NumberRepositoryInterface::class, NumberRepository::class);
        $this->app->bind(RedirectRepositoryInterface::class, RedirectRepository::class);
        $this->app->bind(WebsiteRepositoryInterface::class, WebsiteRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);
        $this->app->bind(ImageRepositoryInterface::class, ImageRepository::class);
        $this->app->bind(StatusRepositoryInterface::class, StatusRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(AttributeRepositoryInterface::class, AttributeRepository::class);
        $this->app->bind(WebsiteConfigRepositoryInterface::class, WebsiteConfigRepository::class);
        $this->app->bind(EntityRepositoryInterface::class, EntityRepository::class);
        $this->app->bind(FieldMapRepositoryInterface::class, FieldMapRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(DealerLocationRepositoryInterface::class, DealerLocationRepository::class);
        $this->app->bind(InteractionsRepositoryInterface::class, InteractionsRepository::class);
        $this->app->bind(EmailHistoryRepositoryInterface::class, EmailHistoryRepository::class);
        $this->app->bind(ManufacturerRepositoryInterface::class, ManufacturerRepository::class);

        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);

        $this->app->bind(CostModifierRepositoryInterface::class, CostModifierRepository::class);
        $this->app->bind(MakesRepositoryInterface::class, MakesRepository::class);
        $this->app->bind(VehiclesRepositoryInterface::class, VehiclesRepository::class);
        $this->app->bind(LogServiceInterface::class, LogService::class);

        $this->app->bind(AutoAssignServiceInterface::class, AutoAssignService::class);

        $this->app->bind(DealerProxyRepositoryInterface::class, DealerProxyRedisRepository::class);
    }

}
