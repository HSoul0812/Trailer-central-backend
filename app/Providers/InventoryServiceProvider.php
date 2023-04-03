<?php

namespace App\Providers;

use App\Contracts\LoggerServiceInterface;
use App\Jobs\ElasticSearch\Cache\InvalidateCacheJob;
use App\Jobs\Inventory\ReIndexInventoriesByDealerLocationJob;
use App\Jobs\Inventory\ReIndexInventoriesByDealersJob;
use App\Repositories\Bulk\Inventory\BulkDownloadRepository;
use App\Repositories\Bulk\Inventory\BulkDownloadRepositoryInterface;
use App\Repositories\Bulk\Inventory\BulkUploadRepository;
use App\Repositories\Bulk\Inventory\BulkUploadRepositoryInterface;
use App\Repositories\Inventory\DeletedInventoryRepository;
use App\Repositories\Inventory\DeletedInventoryRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseRedisCache;
use App\Services\ElasticSearch\Cache\RedisResponseCache;
use App\Services\ElasticSearch\Cache\RedisResponseCacheKey;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;
use App\Services\Export\Inventory\Bulk\BulkDownloadJobService;
use App\Services\Export\Inventory\Bulk\BulkDownloadJobServiceInterface;
use App\Services\Export\Inventory\Bulk\BulkPdfJobService;
use App\Services\Export\Inventory\Bulk\BulkPdfJobServiceInterface;
use App\Http\Clients\ElasticSearch\ElasticSearchClient;
use App\Http\Clients\ElasticSearch\ElasticSearchClientInterface;
use App\Repositories\Inventory\AttributeRepository;
use App\Repositories\Inventory\AttributeRepositoryInterface;
use App\Repositories\Inventory\AttributeValueRepository;
use App\Repositories\Inventory\AttributeValueRepositoryInterface;
use App\Repositories\Inventory\CategoryRepository;
use App\Repositories\Inventory\CategoryRepositoryInterface;
use App\Repositories\Inventory\CustomOverlay\CustomOverlayRepository;
use App\Repositories\Inventory\CustomOverlay\CustomOverlayRepositoryInterface;
use App\Repositories\Inventory\FileRepository;
use App\Repositories\Inventory\FileRepositoryInterface;
use App\Repositories\Inventory\Floorplan\PaymentRepository;
use App\Repositories\Inventory\Floorplan\PaymentRepositoryInterface;
use App\Repositories\Inventory\Floorplan\VendorRepository as FloorplanVendorRepository;
use App\Repositories\Inventory\Floorplan\VendorRepositoryInterface as FloorplanVendorRepositoryInterface;
use App\Repositories\Inventory\ImageRepository;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Repositories\Inventory\InventoryFilterRepository;
use App\Repositories\Inventory\InventoryFilterRepositoryInterface;
use App\Repositories\Inventory\InventoryHistoryRepository;
use App\Repositories\Inventory\InventoryHistoryRepositoryInterface;
use App\Repositories\Inventory\InventoryRepository;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Inventory\ManufacturerRepository;
use App\Repositories\Inventory\ManufacturerRepositoryInterface;
use App\Repositories\Inventory\Manufacturers\BrandRepository;
use App\Repositories\Inventory\Manufacturers\BrandRepositoryInterface;
use App\Repositories\Inventory\Packages\PackageRepository;
use App\Repositories\Inventory\Packages\PackageRepositoryInterface;
use App\Repositories\Inventory\StatusRepository;
use App\Repositories\Inventory\StatusRepositoryInterface;
use App\Rules\Inventory\BrandExists;
use App\Rules\Inventory\BrandValid;
use App\Rules\Inventory\CategoryExists;
use App\Rules\Inventory\CategoryValid;
use App\Rules\Inventory\Floorplan\PaymentUUIDValid;
use App\Rules\Inventory\ManufacturerExists;
use App\Rules\Inventory\ManufacturerValid;
use App\Rules\Inventory\MfgIdExists;
use App\Rules\Inventory\MfgNameValid;
use App\Rules\Inventory\QuotesNotExist;
use App\Rules\Inventory\UniqueStock;
use App\Rules\Inventory\ValidInventory;
use App\Rules\Inventory\VendorExists;
use App\Services\ElasticSearch\Inventory\Builders\QueryBuilder;
use App\Services\ElasticSearch\Inventory\FieldMapperService;
use App\Services\ElasticSearch\Inventory\InventoryFieldMapperServiceInterface;
use App\Services\ElasticSearch\Inventory\InventoryQueryBuilderInterface;
use App\Services\Import\Inventory\CsvImportService;
use App\Services\Inventory\ImageService;
use App\Services\Inventory\ImageServiceInterface;
use App\Services\Import\Inventory\CsvImportServiceInterface;
use App\Services\Inventory\CustomOverlay\CustomOverlayService;
use App\Services\Inventory\CustomOverlay\CustomOverlayServiceInterface;
use App\Services\Inventory\Floorplan\PaymentService;
use App\Services\Inventory\Floorplan\PaymentServiceInterface;
use App\Services\Inventory\InventoryAttributeService;
use App\Services\Inventory\InventoryAttributeServiceInterface;
use App\Services\Inventory\InventoryService;
use App\Services\Inventory\InventoryServiceInterface;
use App\Services\Inventory\Packages\PackageService;
use App\Services\Inventory\Packages\PackageServiceInterface;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;
use Validator;

/**
 * Class InventoryServiceProvider
 * @package App\Providers
 */
class InventoryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Validator::extend('inventory_valid', ValidInventory::class . '@passes');
        Validator::extend('inventory_mfg_exists', ManufacturerExists::class . '@passes');
        Validator::extend('inventory_mfg_valid', ManufacturerValid::class . '@passes');
        Validator::extend('inventory_mfg_id_valid', MfgIdExists::class . '@passes');
        Validator::extend('inventory_mfg_name_valid', MfgNameValid::class . '@passes');
        Validator::extend('inventory_cat_exists', CategoryExists::class . '@passes');
        Validator::extend('inventory_cat_valid', CategoryValid::class . '@passes');
        Validator::extend('inventory_brand_exists', BrandExists::class . '@passes');
        Validator::extend('inventory_brand_valid', BrandValid::class . '@passes');
        Validator::extend('inventory_unique_stock', UniqueStock::class . '@passes');
        Validator::extend('inventory_quotes_not_exist', QuotesNotExist::class . '@passes');
        Validator::extend('vendor_exists', VendorExists::class . '@passes');
        Validator::extend('payment_uuid_valid', PaymentUUIDValid::class . '@validate');
    }

    public function register(): void
    {
        $this->app->bind(DeletedInventoryRepositoryInterface::class, DeletedInventoryRepository::class);
        $this->app->bind(BulkUploadRepositoryInterface::class, BulkUploadRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(InventoryServiceInterface::class, InventoryService::class);
        $this->app->bind(InventoryHistoryRepositoryInterface::class, InventoryHistoryRepository::class);
        $this->app->bind(AttributeRepositoryInterface::class, AttributeRepository::class);
        $this->app->bind(AttributeValueRepositoryInterface::class, AttributeValueRepository::class);
        $this->app->bind(InventoryAttributeServiceInterface::class, InventoryAttributeService::class);
        $this->app->bind(CustomOverlayServiceInterface::class, CustomOverlayService::class);
        $this->app->bind(CustomOverlayRepositoryInterface::class, CustomOverlayRepository::class);
        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);
        $this->app->bind(ImageRepositoryInterface::class, ImageRepository::class);
        $this->app->bind(StatusRepositoryInterface::class, StatusRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(PackageRepositoryInterface::class, PackageRepository::class);
        $this->app->bind(PackageServiceInterface::class, PackageService::class);
        $this->app->bind(FloorplanVendorRepositoryInterface::class, FloorplanVendorRepository::class);
        $this->app->bind(ManufacturerRepositoryInterface::class, ManufacturerRepository::class);
        $this->app->bind(CsvImportServiceInterface::class, CsvImportService::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);

        $this->app->bind(ElasticSearchClientInterface::class, ElasticSearchClient::class);
        $this->app->bind(InventoryQueryBuilderInterface::class, QueryBuilder::class);
        $this->app->bind(InventoryFilterRepositoryInterface::class, InventoryFilterRepository::class);
        $this->app->bind(InventoryFieldMapperServiceInterface::class, FieldMapperService::class);

        $this->app->bind(
            \App\Services\ElasticSearch\Inventory\InventoryServiceInterface::class,
            \App\Services\ElasticSearch\Inventory\InventoryService::class
        );

        $this->app->bind(BulkDownloadRepositoryInterface::class, BulkDownloadRepository::class);
        $this->app->bind(BulkDownloadJobServiceInterface::class, BulkDownloadJobService::class);
        $this->app->bind(BulkPdfJobServiceInterface::class, BulkPdfJobService::class);
        $this->app->bind(BulkUploadRepositoryInterface::class, BulkUploadRepository::class);

        $this->app->bind(ImageServiceInterface::class, ImageService::class);

        $this->app->bind(ResponseCacheKeyInterface::class, RedisResponseCacheKey::class);

        $this->app->bind(InventoryResponseCacheInterface::class, function (): InventoryResponseRedisCache {
            return new InventoryResponseRedisCache(
                $this->app->make(ResponseCacheKeyInterface::class),
                new RedisResponseCache(Redis::connection('sdk-search-cache')->client()),
                new RedisResponseCache(Redis::connection('sdk-single-cache')->client())
            );
        });

        $this->app->bindMethod(InvalidateCacheJob::class . '@handle', function (InvalidateCacheJob $job): void {
            $job->handle($this->app->make(InventoryResponseCacheInterface::class));
        });

        $this->app->bindMethod(ReIndexInventoriesByDealersJob::class . '@handle', function (ReIndexInventoriesByDealersJob $job): void {
            $job->handle(
                $this->app->make(InventoryResponseCacheInterface::class),
                $this->app->make(ResponseCacheKeyInterface::class),
                $this->app->make(LoggerServiceInterface::class)
            );
        });

        $this->app->bindMethod(ReIndexInventoriesByDealerLocationJob::class . '@handle', function (ReIndexInventoriesByDealerLocationJob $job): void {
            $job->handle(
                $this->app->make(DealerLocationRepositoryInterface::class),
                $this->app->make(InventoryResponseCacheInterface::class),
                $this->app->make(ResponseCacheKeyInterface::class),
                $this->app->make(LoggerServiceInterface::class)
            );
        });
    }
}
