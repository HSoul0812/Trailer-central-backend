<?php

namespace App\Providers;

use App\Contracts\LoggerServiceInterface;
use App\Models\Feed\Mapping\Incoming\DealerIncomingMapping;
use App\Nova\Observer\DealerIncomingMappingObserver;
use App\Repositories\Bulk\Parts\BulkUploadRepository;
use App\Repositories\Bulk\Parts\BulkUploadRepositoryInterface;
use App\Repositories\Common\MonitoredJobRepository;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRepository;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRoleRepository;
use App\Repositories\CRM\User\CrmUserRoleRepositoryInterface;
use App\Repositories\Dms\StockRepository;
use App\Repositories\Dms\StockRepositoryInterface;
use App\Repositories\Integration\CVR\CvrFileRepository;
use App\Repositories\Integration\CVR\CvrFileRepositoryInterface;
use App\Repositories\Inventory\CategoryRepository;
use App\Repositories\Inventory\CategoryRepositoryInterface;
use App\Repositories\Inventory\AttributeRepository;
use App\Repositories\Inventory\AttributeRepositoryInterface;
use App\Repositories\Inventory\FileRepository;
use App\Repositories\Inventory\FileRepositoryInterface;
use App\Repositories\Inventory\ImageRepository;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Repositories\Inventory\InventoryHistoryRepository;
use App\Repositories\Inventory\InventoryHistoryRepositoryInterface;
use App\Repositories\Inventory\StatusRepository;
use App\Repositories\Inventory\StatusRepositoryInterface;
use App\Repositories\Dms\PurchaseOrder\PurchaseOrderReceiptRepository;
use App\Repositories\Dms\PurchaseOrder\PurchaseOrderReceiptRepositoryInterface;
use App\Repositories\Dms\ServiceOrderRepository;
use App\Repositories\Dms\ServiceOrderRepositoryInterface;
use App\Repositories\Dms\Quickbooks\AccountRepository;
use App\Repositories\Dms\Quickbooks\AccountRepositoryInterface;
use App\Repositories\Dms\Quickbooks\ExpenseRepository;
use App\Repositories\Dms\Quickbooks\ExpenseRepositoryInterface;
use App\Repositories\Dms\Quickbooks\ItemNewRepository;
use App\Repositories\Dms\Quickbooks\ItemNewRepositoryInterface;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepository;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepositoryInterface;
use App\Repositories\Pos\SaleRepository;
use App\Repositories\Pos\SaleRepositoryInterface;
use App\Repositories\Inventory\InventoryRepository;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Inventory\ManufacturerRepository;
use App\Repositories\Inventory\ManufacturerRepositoryInterface;
use App\Repositories\Showroom\ShowroomFieldsMappingRepository;
use App\Repositories\Showroom\ShowroomFieldsMappingRepositoryInterface;
use App\Repositories\Pos\SalesReportRepository;
use App\Repositories\Pos\SalesReportRepositoryInterface;
use App\Repositories\User\DealerLocationQuoteFeeRepository;
use App\Repositories\User\DealerLocationQuoteFeeRepositoryInterface;
use App\Repositories\User\NewDealerUserRepository;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Repositories\User\NewUserRepository;
use App\Repositories\User\NewUserRepositoryInterface;
use App\Repositories\Website\DealerProxyRedisRepository;
use App\Repositories\Website\DealerProxyRepositoryInterface;
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
use App\Repositories\Parts\CostModifierRepository;
use App\Repositories\Parts\CostModifierRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\User\DealerPasswordResetRepositoryInterface;
use App\Repositories\User\DealerPasswordResetRepository;
use App\Services\Integration\CVR\CvrFileService;
use App\Services\Integration\CVR\CvrFileServiceInterface;
use App\Services\User\PasswordResetServiceInterface;
use App\Services\User\PasswordResetService;
use App\Repositories\User\DealerLocationRepository;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Repositories\Inventory\Floorplan\VendorRepository as FloorplanVendorRepository;
use App\Repositories\Inventory\Floorplan\VendorRepositoryInterface as FloorplanVendorRepositoryInterface;
use App\Repositories\System\EmailRepository;
use App\Repositories\System\EmailRepositoryInterface;
use App\Services\Common\EncrypterServiceInterface;
use App\Services\Common\LoggerService;
use App\Services\Common\MonitoredGenericJobServiceInterface;
use App\Services\Common\MonitoredJobService;
use App\Services\Common\SPLEncrypterService;
use App\Services\Inventory\Floorplan\PaymentServiceInterface;
use App\Services\Inventory\Floorplan\PaymentService;
use App\Services\Inventory\InventoryService;
use App\Services\Inventory\InventoryServiceInterface;
use App\Services\Pos\CustomSalesReportExporterService;
use App\Services\Pos\CustomSalesReportExporterServiceInterface;
use App\Services\Export\DomPdfExporterService;
use App\Services\Export\DomPdfExporterServiceInterface;
use App\Services\User\DealerOptionsService;
use App\Services\User\DealerOptionsServiceInterface;
use App\Services\Website\Log\LogServiceInterface;
use App\Services\Website\Log\LogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
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
        \Validator::extend('checkbox', 'App\Rules\Checkbox@passes');
        \Validator::extend('dealer_location_valid', 'App\Rules\User\ValidDealerLocation@passes');
        \Validator::extend('website_valid', 'App\Rules\Website\ValidWebsite@passes');
        \Validator::extend('inventory_valid', 'App\Rules\Inventory\ValidInventory@passes');
        \Validator::extend('inventory_mfg_exists', 'App\Rules\Inventory\ManufacturerExists@passes');
        \Validator::extend('inventory_mfg_valid', 'App\Rules\Inventory\ManufacturerValid@passes');
        \Validator::extend('inventory_mfg_id_valid', 'App\Rules\Inventory\MfgIdExists@passes');
        \Validator::extend('inventory_mfg_name_valid', 'App\Rules\Inventory\MfgNameValid@passes');
        \Validator::extend('inventory_cat_exists', 'App\Rules\Inventory\CategoryExists@passes');
        \Validator::extend('inventory_cat_valid', 'App\Rules\Inventory\CategoryValid@passes');
        \Validator::extend('inventory_brand_exists', 'App\Rules\Inventory\BrandExists@passes');
        \Validator::extend('inventory_brand_valid', 'App\Rules\Inventory\BrandValid@passes');
        \Validator::extend('inventory_unique_stock', 'App\Rules\Inventory\UniqueStock@passes');
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
        \Validator::extend('valid_token_type', 'App\Rules\Integration\Auth\ValidTokenType@passes');
        \Validator::extend('valid_relation_type', 'App\Rules\Integration\Auth\ValidRelationType@passes');
        \Validator::extend('valid_part_order_status', 'App\Rules\Parts\ValidOrderStatus@passes');
        \Validator::extend('valid_part_fulfillment', 'App\Rules\Parts\ValidFulfillment@passes');
        \Validator::extend('customer_name_unique', 'App\Rules\Dms\Quickbooks\CustomerNameUnique@validate');
        \Validator::extend('payment_uuid_valid', 'App\Rules\Inventory\Floorplan\PaymentUUIDValid@validate');
        \Validator::extend('stock_type_valid', 'App\Rules\Bulks\Parts\StockTypeValid@passes');

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

            // utilities
            __DIR__ . '/../../database/migrations/utilities',

            // configuration tables
            __DIR__ . '/../../database/migrations/config',
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
        $this->app->bind(BulkUploadRepositoryInterface::class, BulkUploadRepository::class);
        $this->app->bind('App\Repositories\Inventory\Floorplan\PaymentRepositoryInterface', 'App\Repositories\Inventory\Floorplan\PaymentRepository');
        $this->app->bind(ShowroomRepositoryInterface::class, ShowroomRepository::class);
        $this->app->bind(ShowroomFieldsMappingRepositoryInterface::class, ShowroomFieldsMappingRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
        $this->app->bind(RedirectRepositoryInterface::class, RedirectRepository::class);
        $this->app->bind(WebsiteRepositoryInterface::class, WebsiteRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(InventoryServiceInterface::class, InventoryService::class);
        $this->app->bind(InventoryHistoryRepositoryInterface::class, InventoryHistoryRepository::class);
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);
        $this->app->bind(ImageRepositoryInterface::class, ImageRepository::class);
        $this->app->bind(StatusRepositoryInterface::class, StatusRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(AttributeRepositoryInterface::class, AttributeRepository::class);
        $this->app->bind(WebsiteConfigRepositoryInterface::class, WebsiteConfigRepository::class);
        $this->app->bind(EntityRepositoryInterface::class, EntityRepository::class);
        $this->app->bind(FieldMapRepositoryInterface::class, FieldMapRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CrmUserRepositoryInterface::class, CrmUserRepository::class);
        $this->app->bind(CrmUserRoleRepositoryInterface::class, CrmUserRoleRepository::class);
        $this->app->bind(DealerLocationRepositoryInterface::class, DealerLocationRepository::class);
        $this->app->bind(DealerLocationQuoteFeeRepositoryInterface::class, DealerLocationQuoteFeeRepository::class);
        $this->app->bind(NewUserRepositoryInterface::class, NewUserRepository::class);
        $this->app->bind(NewDealerUserRepositoryInterface::class, NewDealerUserRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->bind(SaleRepositoryInterface::class, SaleRepository::class);
        $this->app->bind(SalesReportRepositoryInterface::class, SalesReportRepository::class);
        $this->app->bind(CustomSalesReportExporterServiceInterface::class, CustomSalesReportExporterService::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(PurchaseOrderReceiptRepositoryInterface::class, PurchaseOrderReceiptRepository::class);
        $this->app->bind(ServiceOrderRepositoryInterface::class, ServiceOrderRepository::class);
        $this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);
        $this->app->bind(ExpenseRepositoryInterface::class, ExpenseRepository::class);
        $this->app->bind(ItemNewRepositoryInterface::class, ItemNewRepository::class);
        $this->app->bind(QuickbookApprovalRepositoryInterface::class, QuickbookApprovalRepository::class);
        $this->app->bind(ManufacturerRepositoryInterface::class, ManufacturerRepository::class);
        $this->app->bind(FloorplanVendorRepositoryInterface::class, FloorplanVendorRepository::class);

        $this->app->bind(CostModifierRepositoryInterface::class, CostModifierRepository::class);
        $this->app->bind(MakesRepositoryInterface::class, MakesRepository::class);
        $this->app->bind(VehiclesRepositoryInterface::class, VehiclesRepository::class);
        $this->app->bind(LogServiceInterface::class, LogService::class);
        $this->app->bind(EncrypterServiceInterface::class, SPLEncrypterService::class);

        $this->app->bind(DealerProxyRepositoryInterface::class, DealerProxyRedisRepository::class);

        $this->app->bind(DealerOptionsServiceInterface::class, DealerOptionsService::class);

        $this->app->bind(EmailRepositoryInterface::class, EmailRepository::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);

        $this->app->bind(DealerPasswordResetRepositoryInterface::class, DealerPasswordResetRepository::class);
        $this->app->bind(PasswordResetServiceInterface::class, PasswordResetService::class);

        $this->app->bind(MonitoredGenericJobServiceInterface::class, MonitoredJobService::class);
        $this->app->bind(MonitoredJobRepositoryInterface::class, MonitoredJobRepository::class);
        $this->app->bind(CvrFileRepositoryInterface::class, CvrFileRepository::class);
        $this->app->bind(CvrFileServiceInterface::class, CvrFileService::class);

        $this->app->singleton(LoggerServiceInterface::class, LoggerService::class);

        $this->app->bind(DomPdfExporterServiceInterface::class, DomPdfExporterService::class);

        $this->app->bind(StockRepositoryInterface::class, StockRepository::class);
    }
}
