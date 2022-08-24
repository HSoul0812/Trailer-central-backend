<?php

namespace App\Providers;

use App\Contracts\LoggerServiceInterface;
use App\Helpers\ImageHelper;
use App\Helpers\SanitizeHelper;
use App\Http\Controllers\v1\File\FileController;
use App\Http\Controllers\v1\File\ImageController;
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
use App\Repositories\CRM\User\EmployeeRepository;
use App\Repositories\CRM\User\EmployeeRepositoryInterface;
use App\Repositories\CRM\User\TimeClockRepository;
use App\Repositories\CRM\User\TimeClockRepositoryInterface;
use App\Repositories\Dms\Pos\RegisterRepository;
use App\Repositories\Dms\Pos\RegisterRepositoryInterface;
use App\Repositories\Dms\StockRepository;
use App\Repositories\Dms\StockRepositoryInterface;
use App\Repositories\Feed\Mapping\Incoming\ApiEntityReferenceRepository;
use App\Repositories\Feed\Mapping\Incoming\ApiEntityReferenceRepositoryInterface;
use App\Repositories\Inventory\CategoryRepository;
use App\Repositories\Inventory\CategoryRepositoryInterface;
use App\Repositories\Inventory\AttributeRepository;
use App\Repositories\Inventory\AttributeRepositoryInterface;
use App\Repositories\Inventory\CustomOverlay\CustomOverlayRepository;
use App\Repositories\Inventory\CustomOverlay\CustomOverlayRepositoryInterface;
use App\Repositories\Inventory\FileRepository;
use App\Repositories\Inventory\FileRepositoryInterface;
use App\Repositories\Inventory\ImageRepository;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Repositories\Inventory\InventoryHistoryRepository;
use App\Repositories\Inventory\InventoryHistoryRepositoryInterface;
use App\Repositories\Inventory\Packages\PackageRepository;
use App\Repositories\Inventory\Packages\PackageRepositoryInterface;
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
use App\Repositories\Showroom\ShowroomBulkUpdateRepository;
use App\Repositories\Showroom\ShowroomBulkUpdateRepositoryInterface;
use App\Repositories\Showroom\ShowroomFieldsMappingRepository;
use App\Repositories\Showroom\ShowroomFieldsMappingRepositoryInterface;
use App\Repositories\Pos\SalesReportRepository;
use App\Repositories\Pos\SalesReportRepositoryInterface;
use App\Repositories\Subscription\SubscriptionRepository;
use App\Repositories\Subscription\SubscriptionRepositoryInterface;
use App\Repositories\User\DealerLocationMileageFeeRepository;
use App\Repositories\User\DealerLocationMileageFeeRepositoryInterface;
use App\Repositories\User\DealerLocationQuoteFeeRepository;
use App\Repositories\User\DealerLocationQuoteFeeRepositoryInterface;
use App\Repositories\User\DealerLocationSalesTaxItemRepository;
use App\Repositories\User\DealerLocationSalesTaxItemRepositoryInterface;
use App\Repositories\User\DealerLocationSalesTaxRepository;
use App\Repositories\User\DealerLocationSalesTaxRepositoryInterface;
use App\Repositories\User\Integration\DealerIntegrationRepository;
use App\Repositories\User\Integration\DealerIntegrationRepositoryInterface;
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
use App\Repositories\Inventory\AttributeValueRepository;
use App\Repositories\Inventory\AttributeValueRepositoryInterface;
use App\Repositories\Parts\CostModifierRepository;
use App\Repositories\Parts\CostModifierRepositoryInterface;
use App\Repositories\User\DealerPasswordResetRepositoryInterface;
use App\Repositories\User\DealerPasswordResetRepository;
use App\Services\CRM\Interactions\InteractionEmailService;
use App\Services\CRM\User\TimeClockService;
use App\Services\CRM\User\TimeClockServiceInterface;
use App\Services\Dms\Bills\BillService;
use App\Services\Dms\Bills\BillServiceInterface;
use App\Services\Dms\Pos\RegisterService;
use App\Services\Dms\Pos\RegisterServiceInterface;
use App\Services\File\FileService;
use App\Services\File\FileServiceInterface;
use App\Services\File\ImageService;
use App\Services\Inventory\CustomOverlay\CustomOverlayService;
use App\Services\Inventory\CustomOverlay\CustomOverlayServiceInterface;
use App\Services\Subscription\StripeService;
use App\Services\Subscription\StripeServiceInterface;
use App\Services\User\DealerIntegrationService;
use App\Services\User\DealerIntegrationServiceInterface;
use App\Services\Inventory\Packages\PackageService;
use App\Services\Inventory\Packages\PackageServiceInterface;
use App\Services\User\DealerLocationService;
use App\Services\User\DealerLocationServiceInterface;
use App\Services\User\PasswordResetServiceInterface;
use App\Services\User\PasswordResetService;
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
use App\Repositories\Inventory\Manufacturers\BrandRepositoryInterface;
use App\Repositories\Inventory\Manufacturers\BrandRepository;
use App\Repositories\Marketing\Facebook\ErrorRepository;
use App\Repositories\Marketing\Facebook\ErrorRepositoryInterface;
use App\Services\Inventory\InventoryAttributeService;
use App\Services\Inventory\InventoryAttributeServiceInterface;
use App\Services\Pos\CustomSalesReportExporterService;
use App\Services\Pos\CustomSalesReportExporterServiceInterface;
use App\Services\Website\Log\LogServiceInterface;
use App\Services\Website\Log\LogService;
use App\Services\Website\WebsiteConfigService;
use App\Services\Website\WebsiteConfigServiceInterface;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;
use Propaganistas\LaravelPhone\PhoneServiceProvider;

use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \URL::forceScheme('https');
        \Validator::extend('price_format', 'App\Rules\PriceFormat@passes');
        \Validator::extend('checkbox', 'App\Rules\Checkbox@passes');
        \Validator::extend('dealer_location_valid', 'App\Rules\User\ValidDealerLocation@passes');
        \Validator::extendDependent('permission_level_valid', 'App\Rules\User\ValidPermissionLevel@passes');
        \Validator::extend('unique_dealer_location_name', 'App\Rules\User\ValidDealerLocationName@passes');
        \Validator::extend('tax_calculator_valid', 'App\Rules\User\ValidTaxCalculator@passes');
        \Validator::extend('website_valid', 'App\Rules\Website\ValidWebsite@passes');
        \Validator::extend('website_exists', 'App\Rules\Website\WebsiteExists@passes');
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
        \Validator::extend('inventory_quotes_not_exist', 'App\Rules\Inventory\QuotesNotExist@passes');
        \Validator::extend('lead_exists', 'App\Rules\CRM\Leads\LeadExists@passes');
        \Validator::extend('lead_type_valid', 'App\Rules\CRM\Leads\ValidLeadType@passes');
        \Validator::extend('lead_status_valid', 'App\Rules\CRM\Leads\ValidLeadStatus@passes');
        \Validator::extend('lead_source_valid', 'App\Rules\CRM\Leads\ValidLeadSource@passes');
        \Validator::extend('inquiry_type_valid', 'App\Rules\CRM\Leads\ValidInquiryType@passes');
        \Validator::extend('inquiry_email_valid', 'App\Rules\CRM\Leads\ValidInquiryEmail@passes');
        \Validator::extend('sales_person_valid', 'App\Rules\CRM\User\ValidSalesPerson@passes');
        \Validator::extend('sales_security_type', 'App\Rules\CRM\User\ValidSecurityType@passes');
        \Validator::extend('sales_auth_type', 'App\Rules\CRM\User\ValidAuthType@passes');
        \Validator::extend('valid_smtp_email', 'App\Rules\CRM\User\ValidSmtpEmail@passes');
        \Validator::extend('interaction_type_valid', 'App\Rules\CRM\Interactions\ValidInteractionType@passes');
        \Validator::extend('campaign_action_valid', 'App\Rules\CRM\Email\CampaignActionValid@passes');
        \Validator::extend('text_exists', 'App\Rules\CRM\Text\TextExists@passes');
        \Validator::extend('text_template_exists', 'App\Rules\CRM\Text\TemplateExists@passes');
        \Validator::extend('email_template_exists', 'App\Rules\CRM\Email\TemplateExists@passes');
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
        \Validator::extend('unit_sale_exists', 'App\Rules\Dms\UnitSaleExists@passes');
        \Validator::extend('valid_clapp_profile', 'App\Rules\Marketing\Craigslist\ValidProfile@passes');
        \Validator::extend('valid_include', 'App\Rules\ValidInclude@validate');
        \Validator::extend('location_belongs_to_dealer', 'App\Rules\Locations\LocationBelongsToDealer@passes');
        \Validator::extend('bin_belongs_to_dealer', 'App\Rules\Bins\BinBelongsToDealer@passes');
        \Validator::extend('valid_location_email', 'App\Rules\DealerLocation\EmailValid@passes');
        \Validator::extend('valid_password', 'App\Rules\User\ValidPassword@passes');

        Builder::macro('whereLike', function($attributes, string $searchTerm) {
            foreach(array_wrap($attributes) as $attribute) {
               $this->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
            }

            return $this;
        });

        Nova::serving(function () {
            DealerIncomingMapping::observe(DealerIncomingMappingObserver::class);
        });

        // Increase default database character set length (Specified key was too long)
        Schema::defaultStringLength(191);

        // Add Migration Directories Recursively
        $mainPath = database_path('migrations');
        $directories = glob($mainPath . '/*' , GLOB_ONLYDIR);
        $paths = array_merge([$mainPath], $directories);

        $this->loadMigrationsFrom($paths);

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
        $this->app->bind('App\Repositories\Bulk\Inventory\BulkUploadRepositoryInterface', 'App\Repositories\Bulk\Inventory\BulkUploadRepository');
        $this->app->bind('App\Repositories\Website\Parts\FilterRepositoryInterface', 'App\Repositories\Website\Parts\FilterRepository');
        $this->app->bind('App\Repositories\Website\Blog\PostRepositoryInterface', 'App\Repositories\Website\Blog\PostRepository');
        $this->app->bind('App\Repositories\Subscription\SubscriptionRepositoryInterface', 'App\Repositories\Subscription\SubscriptionRepository');
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
        $this->app->bind(EntityRepositoryInterface::class, EntityRepository::class);
        $this->app->bind(FieldMapRepositoryInterface::class, FieldMapRepository::class);
        $this->app->bind(CrmUserRepositoryInterface::class, CrmUserRepository::class);
        $this->app->bind(CrmUserRoleRepositoryInterface::class, CrmUserRoleRepository::class);
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
        $this->app->bind(BillServiceInterface::class, BillService::class);

        $this->app->bind(CostModifierRepositoryInterface::class, CostModifierRepository::class);
        $this->app->bind(MakesRepositoryInterface::class, MakesRepository::class);
        $this->app->bind(VehiclesRepositoryInterface::class, VehiclesRepository::class);
        $this->app->bind(LogServiceInterface::class, LogService::class);
        $this->app->bind(EncrypterServiceInterface::class, SPLEncrypterService::class);

        $this->app->bind(DealerProxyRepositoryInterface::class, DealerProxyRedisRepository::class);

        $this->app->bind(EmailRepositoryInterface::class, EmailRepository::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);

        $this->app->bind(DealerPasswordResetRepositoryInterface::class, DealerPasswordResetRepository::class);
        $this->app->bind(PasswordResetServiceInterface::class, PasswordResetService::class);

        $this->app->bind(MonitoredGenericJobServiceInterface::class, MonitoredJobService::class);
        $this->app->bind(MonitoredJobRepositoryInterface::class, MonitoredJobRepository::class);

        $this->app->singleton(LoggerServiceInterface::class, LoggerService::class);

        $this->app->bind(StockRepositoryInterface::class, StockRepository::class);

        $this->app->bind(ApiEntityReferenceRepositoryInterface::class, ApiEntityReferenceRepository::class);

        $this->app->bind(DealerIntegrationServiceInterface::class, DealerIntegrationService::class);
        $this->app->bind(DealerIntegrationRepositoryInterface::class, DealerIntegrationRepository::class);
        $this->app->bind(DealerLocationServiceInterface::class, DealerLocationService::class);
        $this->app->bind(DealerLocationSalesTaxItemRepositoryInterface::class, DealerLocationSalesTaxItemRepository::class);
        $this->app->bind(DealerLocationSalesTaxRepositoryInterface::class, DealerLocationSalesTaxRepository::class);
        $this->app->bind(DealerLocationQuoteFeeRepositoryInterface::class, DealerLocationQuoteFeeRepository::class);
        $this->app->bind(DealerLocationMileageFeeRepositoryInterface::class, DealerLocationMileageFeeRepository::class);

        $this->app->bind(PackageRepositoryInterface::class, PackageRepository::class);
        $this->app->bind(PackageServiceInterface::class, PackageService::class);
        $this->app->bind(RegisterRepositoryInterface::class, RegisterRepository::class);
        $this->app->bind(RegisterServiceInterface::class, RegisterService::class);

        $this->app->when(FileController::class)
            ->needs(FileServiceInterface::class)
            ->give(function () {
                return new FileService(app()->make(Client::class), app()->make(SanitizeHelper::class));
            });

        $this->app->when(ImageController::class)
            ->needs(FileServiceInterface::class)
            ->give(function () {
                return new ImageService(app()->make(Client::class), app()->make(SanitizeHelper::class), app()->make(ImageHelper::class));
            });

        $this->app->when(InteractionEmailService::class)
            ->needs(FileServiceInterface::class)
            ->give(function () {
                return new FileService(app()->make(Client::class), app()->make(SanitizeHelper::class));
            });

        $this->app->bind(TimeClockRepositoryInterface::class, TimeClockRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(TimeClockServiceInterface::class, TimeClockService::class);
        $this->app->bind(WebsiteConfigServiceInterface::class, WebsiteConfigService::class);
        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);

        $this->app->bind(AttributeValueRepositoryInterface::class, AttributeValueRepository::class);
        $this->app->bind(InventoryAttributeServiceInterface::class, InventoryAttributeService::class);
        $this->app->bind(CustomOverlayServiceInterface::class, CustomOverlayService::class);
        $this->app->bind(CustomOverlayRepositoryInterface::class, CustomOverlayRepository::class);

        $this->app->bind(ShowroomBulkUpdateRepositoryInterface::class, ShowroomBulkUpdateRepository::class);

        $this->app->bind(ErrorRepositoryInterface::class, ErrorRepository::class);

        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->bind(StripeServiceInterface::class, StripeService::class);

        $this->app->register(PhoneServiceProvider::class);
    }
}
