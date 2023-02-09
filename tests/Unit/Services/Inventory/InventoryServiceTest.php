<?php

namespace Tests\Unit\Services\Inventory;

use App\Exceptions\Inventory\InventoryException;
use App\Jobs\Files\DeleteS3FilesJob;
use App\Models\CRM\Dms\Quickbooks\Bill;
use App\Models\Inventory\File;
use App\Models\Inventory\Image;
use App\Models\Inventory\Inventory;
use App\Models\User\DealerLocation;
use App\Models\User\DealerLocationMileageFee;
use App\Nova\Resources\Inventory\InventoryCategory;
use App\Repositories\Dms\Quickbooks\BillRepositoryInterface;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepositoryInterface;
use App\Repositories\Inventory\CategoryRepositoryInterface;
use App\Repositories\Inventory\FileRepositoryInterface;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Repository;
use App\Repositories\User\DealerLocationMileageFeeRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseRedisCache;
use App\Services\File\DTOs\FileDto;
use App\Services\File\FileService;
use App\Services\File\ImageService;
use App\Services\Inventory\InventoryService;
use App\Services\Inventory\InventoryServiceInterface;
use App\Services\User\GeolocationService;
use App\Services\User\GeoLocationServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Repositories\User\GeoLocationRepositoryInterface;
use App\Contracts\LoggerServiceInterface;
use App\Jobs\Inventory\GenerateOverlayImageJob;
use Illuminate\Support\Facades\Queue;
use App\Models\Inventory\InventoryImage;
use Illuminate\Support\Facades\Storage;
use App\Models\User\User;
use App\Services\Inventory\ImageServiceInterface;
use App\Services\Inventory\ImageService as ImageTableService;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;

/**
 * Test for App\Services\Inventory\InventoryService
 *
 * Class InventoryServiceTest
 * @package Tests\Unit\Services\Inventory
 *
 * @group DW
 * @group DW_INVENTORY
 * @group DW_ELASTICSEARCH
 *
 * @coversDefaultClass \App\Services\Inventory\InventoryService
 */
class InventoryServiceTest extends TestCase
{
    use WithFaker;

    const TEST_DEALER_ID = PHP_INT_MAX;
    const TEST_INVENTORY_ID = PHP_INT_MAX - 1;
    const TEST_VIN = 'test_vin';
    const TEST_MODEL = 'test_model';
    const TEST_TITLE = 'test_tile';
    const TEST_STOCK = 'test_stock';

    /**
     * @var LegacyMockInterface|InventoryRepositoryInterface
     */
    private $inventoryRepositoryMock;

    /**
     * @var LegacyMockInterface|ImageRepositoryInterface
     */
    private $imageRepositoryMock;

    /**
     * @var LegacyMockInterface|FileRepositoryInterface
     */
    private $fileRepositoryMock;

    /**
     * @var LegacyMockInterface|BillRepositoryInterface
     */
    private $billRepositoryMock;

    /**
     * @var LegacyMockInterface|QuickbookApprovalRepositoryInterface
     */
    private $quickbookApprovalRepositoryMock;

    /**
     * @var LegacyMockInterface|ImageService
     */
    private $imageServiceMock;

    /**
     * @var LegacyMockInterface|FileService
     */
    private $fileServiceMock;

    /**
     * @var DealerLocationRepositoryInterface|LegacyMockInterface|Mockery\MockInterface
     */
    private $dealerLocationRepositoryMock;

    /**
     * @var DealerLocationMileageFeeRepositoryInterface|LegacyMockInterface|Mockery\MockInterface
     */
    private $dealerLocationMileageFeeRepositoryMock;

    /**
     * @var CategoryRepositoryInterface|LegacyMockInterface|Mockery\MockInterface
     */
    private $categoryRepositoryMock;

    /**
     * @var LegacyMockInterface|WebsiteConfigRepositoryInterface
     */
    private $websiteConfigRepositoryMock;

    /**
     * @var LegacyMockInterface|LoggerServiceInterface
     */
    private $logServiceMock;

    /**
     * @var LegacyMockInterface|ImageServiceInterface
     */
    private $imageTableServiceMock;

    /**
     * @var LegacyMockInterface|ResponseCacheKeyInterface
     */
    private $responseCacheKeyMock;

    /**
     * @var LegacyMockInterface|InventoryResponseCacheInterface
     */
    private $inventoryResponseCacheMock;

    /**
     * @var LegacyMockInterface|UserRepositoryInterface
     */
    private $userRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryRepositoryMock = Mockery::mock(InventoryRepositoryInterface::class);
        $this->app->instance(InventoryRepositoryInterface::class, $this->inventoryRepositoryMock);

        $this->imageRepositoryMock = Mockery::mock(ImageRepositoryInterface::class);
        $this->app->instance(ImageRepositoryInterface::class, $this->imageRepositoryMock);

        $this->fileRepositoryMock = Mockery::mock(FileRepositoryInterface::class);
        $this->app->instance(FileRepositoryInterface::class, $this->fileRepositoryMock);

        $this->billRepositoryMock = Mockery::mock(BillRepositoryInterface::class);
        $this->app->instance(BillRepositoryInterface::class, $this->billRepositoryMock);

        $this->quickbookApprovalRepositoryMock = Mockery::mock(QuickbookApprovalRepositoryInterface::class);
        $this->app->instance(QuickbookApprovalRepositoryInterface::class, $this->quickbookApprovalRepositoryMock);

        $this->imageServiceMock = Mockery::mock(ImageService::class);
        $this->app->instance(ImageService::class, $this->imageServiceMock);

        $this->fileServiceMock = Mockery::mock(FileService::class);
        $this->app->instance(FileService::class, $this->fileServiceMock);

        $this->dealerLocationRepositoryMock = Mockery::mock(DealerLocationRepositoryInterface::class);
        $this->app->instance(DealerLocationRepositoryInterface::class, $this->dealerLocationRepositoryMock);

        $this->dealerLocationMileageFeeRepositoryMock = Mockery::mock(DealerLocationMileageFeeRepositoryInterface::class);
        $this->app->instance(DealerLocationMileageFeeRepositoryInterface::class, $this->dealerLocationMileageFeeRepositoryMock);

        $this->categoryRepositoryMock = Mockery::mock(CategoryRepositoryInterface::class);
        $this->app->instance(CategoryRepositoryInterface::class, $this->categoryRepositoryMock);

        $this->websiteConfigRepositoryMock = Mockery::mock(WebsiteConfigRepositoryInterface::class);
        $this->app->instance(WebsiteConfigRepositoryInterface::class, $this->websiteConfigRepositoryMock);

        $this->geolocationRepositoryMock = Mockery::mock(GeoLocationRepositoryInterface::class);
        $this->app->instance(GeoLocationRepositoryInterface::class, $this->geolocationRepositoryMock);

        $this->logServiceMock = Mockery::mock(LoggerServiceInterface::class);
        $this->app->instance(LoggerServiceInterface::class, $this->logServiceMock);

        $this->markdownHelper = Mockery::mock(\Parsedown::class);
        $this->app->instance(\Parsedown::class, $this->markdownHelper);

        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepositoryMock);

        $this->imageTableServiceMock = Mockery::mock(ImageTableService::class, [
            $this->imageRepositoryMock,
            $this->userRepositoryMock,
            $this->inventoryRepositoryMock
        ]);
        $this->app->instance(ImageTableService::class, $this->imageTableServiceMock);

        $this->responseCacheKeyMock = Mockery::mock(ResponseCacheKeyInterface::class);
        $this->app->instance(ResponseCacheKeyInterface::class, $this->responseCacheKeyMock);

        $this->inventoryResponseCacheMock = Mockery::mock(InventoryResponseRedisCache::class);
        $this->app->instance(InventoryResponseCacheInterface::class, $this->inventoryResponseCacheMock);

        $this->geolocationServiceMock = Mockery::mock(GeolocationService::class);
        $this->app->instance(GeoLocationServiceInterface::class, $this->geolocationServiceMock);

        Queue::fake();
        Storage::fake('tmp');
    }

    public function tearDown(): void
    {
        Storage::fake('tmp');

        parent::tearDown();
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @group DMS
     * @group DMS_INVENTORY
     *
     * @param array $params
     * @throws BindingResolutionException
     * @throws InventoryException
     */
    public function testCreateWithoutFilesAndBill(array $params)
    {
        $params['new_files'] = [];

        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;
        $inventory->dealer_id = $this->faker->numberBetween(1222, 3333);
        $inventory->shouldReceive('searchable');

        $expectedCacheKey = sprintf('inventories.search.*.dealers:*_%d_*.inventories:*', $inventory->dealer_id);

        $this->inventoryRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($params)
            ->andReturn($inventory);

        $this->inventoryRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('update')
            ->never();

        $this->imageServiceMock
            ->shouldReceive('upload')
            ->never();

        $this->fileServiceMock
            ->shouldReceive('upload')
            ->never();

        $this->billRepositoryMock
            ->shouldReceive('update')
            ->never();

        $this->billRepositoryMock
            ->shouldReceive('create')
            ->never();

        $this->quickbookApprovalRepositoryMock
            ->shouldReceive('deleteByTbPrimaryId')
            ->never();

        Log::shouldReceive('info')
            ->with('Item has been successfully created', ['inventoryId' => $inventory->inventory_id]);

        $this->responseCacheKeyMock->shouldReceive('deleteByDealer')->with($inventory->dealer_id)->andReturn($expectedCacheKey);
        $this->inventoryResponseCacheMock->shouldReceive('forget')->with([$expectedCacheKey]);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertEquals($inventory, $result);
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group Marketing
     * @group Marketing_Overlays
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     *
     * @param array $params
     * @throws BindingResolutionException
     * @throws InventoryException
     */
    public function testCreateWithImages(array $params)
    {
        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;
        $inventory->dealer_id = $this->faker->numberBetween(1222, 3333);
        $inventory->shouldReceive('searchable');

        $expectedCacheKey = sprintf('inventories.search.*.dealers:*_%d_*.inventories:*', $inventory->dealer_id);

        $params['new_images'] = [
            [
                'url' => 'http://test_image1.test',
                'position' => 0
            ],
            [
                'url' => 'http://test_file2.test',
                'position' => 1
            ]
        ];

        $params['overlay_enabled'] = Inventory::OVERLAY_ENABLED_PRIMARY;
        $params['new_files'] = [];

        $overlayEnabledParams = ['skipNotExisting' => true, 'visibility' => config('filesystems.disks.s3.visibility')];

        $this->inventoryRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->with();

        $newImage = new FileDto('path', 'hash');
        $newImageWithOverlay = new FileDto('path_with_overlay', 'hash_with_overlay');

        $this->imageServiceMock
            ->shouldReceive('upload')
            ->once()
            ->with($params['new_images'][1]['url'], $params['title'], self::TEST_DEALER_ID, null, $overlayEnabledParams)
            ->andReturn($newImage);

        $this->imageServiceMock
            ->shouldReceive('upload')
            ->once()
            ->with($params['new_images'][0]['url'], $params['title'], self::TEST_DEALER_ID, null, $overlayEnabledParams)
            ->andReturn($newImage);

        $this->imageServiceMock
            ->shouldReceive('upload')
            ->once()
            ->with($params['new_images'][0]['url'], $params['title'], self::TEST_DEALER_ID, null, $overlayEnabledParams)
            ->andReturn($newImageWithOverlay);

        $this->inventoryRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->withAnyArgs()
            ->andReturn($inventory);

        $this->inventoryRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->with();

        $this->fileServiceMock
            ->shouldReceive('upload')
            ->never();

        $this->billRepositoryMock
            ->shouldReceive('update')
            ->never();

        $this->billRepositoryMock
            ->shouldReceive('create')
            ->never();

        $this->quickbookApprovalRepositoryMock
            ->shouldReceive('deleteByTbPrimaryId')
            ->never();

        $this->responseCacheKeyMock->shouldReceive('deleteByDealer')->with($inventory->dealer_id)->andReturn($expectedCacheKey);
        $this->inventoryResponseCacheMock->shouldReceive('forget')->with([$expectedCacheKey]);

        Log::shouldReceive('info')
            ->with('Item has been successfully created', ['inventoryId' => $inventory->inventory_id]);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertEquals($inventory, $result);
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     *
     * @param array $params
     * @throws BindingResolutionException
     * @throws InventoryException
     */
    public function testCreateWithFiles(array $params)
    {
        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;
        $inventory->dealer_id = $this->faker->numberBetween(1333, 7777);
        $inventory->shouldReceive('searchable');

        $expectedCacheKey = sprintf('inventories.search.*.dealers:*_%d_*.inventories:*', $inventory->dealer_id);

        $params['new_files'] = [
            [
                'url' => 'http://test_file1.test',
                'title' => 'test_file1_title',
            ],
            [
                'url' => 'http://test_file2.test',
                'title' => 'test_file2_title',
            ]
        ];

        $params['hidden_files'] = [
            [
                'url' => 'http://test_file_hidden.test',
                'title' => 'test_file_hidden_title'
            ]
        ];

        $this->inventoryRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->with();

        $expectedParams = $params;
        $expectedParams['new_files'] = array_merge($expectedParams['new_files'], $expectedParams['hidden_files']);
        unset($expectedParams['hidden_files']);

        foreach (array_merge($params['new_files'], $params['hidden_files']) as $key => $file) {
            $newFile = new FileDto('path' . $key, null, 'type' . $key);

            $this->fileServiceMock
                ->shouldReceive('upload')
                ->once()
                ->with($file['url'], $file['title'], self::TEST_DEALER_ID)
                ->andReturn($newFile);

            $expectedParams['new_files'][$key] = array_merge($expectedParams['new_files'][$key], [
                'path' => $newFile->getPath(),
                'type' => $newFile->getMimeType()
            ]);
        }

        $this->inventoryRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($expectedParams)
            ->andReturn($inventory);

        $this->inventoryRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->with();

        $this->imageServiceMock
            ->shouldReceive('upload')
            ->never();

        $this->billRepositoryMock
            ->shouldReceive('update')
            ->never();

        $this->billRepositoryMock
            ->shouldReceive('create')
            ->never();

        $this->quickbookApprovalRepositoryMock
            ->shouldReceive('deleteByTbPrimaryId')
            ->never();

        Log::shouldReceive('info')
            ->with('Item has been successfully created', ['inventoryId' => $inventory->inventory_id]);

        $this->responseCacheKeyMock->shouldReceive('deleteByDealer')->with($inventory->dealer_id)->andReturn($expectedCacheKey);
        $this->inventoryResponseCacheMock->shouldReceive('forget')->with([$expectedCacheKey]);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertEquals($inventory, $result);
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @group DMS
     * @group DMS_INVENTORY
     *
     * @param array $params
     * @throws BindingResolutionException
     * @throws InventoryException
     */
    public function testCreateWithNewBillVendorAndBillExists(array $params)
    {
        $params['new_files'] = [];

        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;
        $inventory->dealer_id = self::TEST_DEALER_ID;
        $inventory->shouldReceive('searchable');

        $expectedCacheKey = sprintf('inventories.search.*.dealers:*_%d_*.inventories:*', $inventory->dealer_id);

        /** @var Bill|LegacyMockInterface $bill */
        $bill = $this->getEloquentMock(Bill::class);
        $bill->id = self::TEST_INVENTORY_ID;

        $billInfo = $this->getBillInfo($inventory, $params);

        $inventoryUpdateParams = $this->getBillInventoryUpdateParams($inventory, $bill, $billInfo);

        $params['add_bill'] = true;

        $this->inventoryRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($params)
            ->andReturn($inventory);

        $this->inventoryRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->with();

        $this->imageServiceMock
            ->shouldReceive('upload')
            ->never();

        $this->fileServiceMock
            ->shouldReceive('upload')
            ->never();

        $this->billRepositoryMock
            ->shouldReceive('create')
            ->never();

        $this->quickbookApprovalRepositoryMock
            ->shouldReceive('deleteByTbPrimaryId')
            ->once()
            ->with($bill->id, 'qb_bills', self::TEST_DEALER_ID);

        $this->billRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($billInfo)
            ->andReturn($bill);

        $this->inventoryRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($inventoryUpdateParams)
            ->andReturn($inventory);

        Log::shouldReceive('info')
            ->with('Item has been successfully created', ['inventoryId' => $inventory->inventory_id]);

        $this->responseCacheKeyMock->shouldReceive('deleteByDealer')->with($inventory->dealer_id)->andReturn($expectedCacheKey);
        $this->inventoryResponseCacheMock->shouldReceive('forget')->with([$expectedCacheKey]);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertEquals($inventory, $result);
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @group DMS
     * @group DMS_INVENTORY
     *
     * @param array $params
     * @throws BindingResolutionException
     * @throws InventoryException
     */
    public function testCreateWithNewBillVendorAndBillNotExists(array $params)
    {
        $params['new_files'] = [];
        unset($params['b_id']);

        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;
        $inventory->dealer_id = self::TEST_DEALER_ID;
        $inventory->shouldReceive('searchable');

        $expectedCacheKey = sprintf('inventories.search.*.dealers:*_%d_*.inventories:*', $inventory->dealer_id);

        /** @var Bill|LegacyMockInterface $bill */
        $bill = $this->getEloquentMock(Bill::class);
        $bill->id = self::TEST_INVENTORY_ID;

        $billInfo = $this->getBillInfo($inventory, $params);
        $billInfo['total'] = 0;

        $inventoryUpdateParams = $this->getBillInventoryUpdateParams($inventory, $bill, $billInfo);
        unset($inventoryUpdateParams['qb_sync_processed']);

        $params['add_bill'] = true;

        $this->inventoryRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($params)
            ->andReturn($inventory);

        $this->inventoryRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->with();

        $this->imageServiceMock
            ->shouldReceive('upload')
            ->never();

        $this->fileServiceMock
            ->shouldReceive('upload')
            ->never();

        $this->billRepositoryMock
            ->shouldReceive('update')
            ->never();

        $this->quickbookApprovalRepositoryMock
            ->shouldReceive('deleteByTbPrimaryId')
            ->never();

        $this->billRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($billInfo)
            ->andReturn($bill);

        $this->inventoryRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($inventoryUpdateParams)
            ->andReturn($inventory);

        Log::shouldReceive('info')
            ->with('Item has been successfully created', ['inventoryId' => $inventory->inventory_id]);

        $this->responseCacheKeyMock->shouldReceive('deleteByDealer')->with($inventory->dealer_id)->andReturn($expectedCacheKey);
        $this->inventoryResponseCacheMock->shouldReceive('forget')->with([$expectedCacheKey]);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertEquals($inventory, $result);
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @group DMS
     * @group DMS_INVENTORY
     *
     * @param array $params
     * @throws BindingResolutionException
     * @throws InventoryException
     */
    public function testCreateWithNewBillWithoutInventoryBill(array $params)
    {
        $params['add_bill'] = true;
        $params['new_files'] = [];
        unset($params['b_vendorId']);
        unset($params['b_isFloorPlan']);

        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;
        $inventory->dealer_id = self::TEST_DEALER_ID;
        $inventory->true_cost = 100;
        $inventory->fp_balance = 50;
        $inventory->fp_vendor = 1;
        $inventory->shouldReceive('searchable');

        $expectedCacheKey = sprintf('inventories.search.*.dealers:*_%d_*.inventories:*', $inventory->dealer_id);

        /** @var Bill|LegacyMockInterface $bill */
        $bill = $this->getEloquentMock(Bill::class);
        $bill->id = self::TEST_INVENTORY_ID;

        $billInfo = $this->getBillInfo($inventory, $params);

        $expectedBillParams = [
            'dealer_id' => $inventory->dealer_id,
            'total' => 0,
            'vendor_id' => $inventory->fp_vendor,
            'status' => 'due',
            'doc_num' => 'fp_auto_' . $inventory->inventory_id
        ];

        $expectedInventoryParams = [
            'inventory_id' => $inventory->inventory_id,
            'send_to_quickbooks' => 1,
            'bill_id' => $bill->id,
            'is_floorplan_bill'=> $billInfo['is_floor_plan']
        ];

        $this->inventoryRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($params)
            ->andReturn($inventory);

        $this->inventoryRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->with();

        $this->imageServiceMock
            ->shouldReceive('upload')
            ->never();

        $this->fileServiceMock
            ->shouldReceive('upload')
            ->never();

        $this->billRepositoryMock
            ->shouldReceive('update')
            ->never();

        $this->quickbookApprovalRepositoryMock
            ->shouldReceive('deleteByTbPrimaryId')
            ->never();

        $this->billRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($expectedBillParams)
            ->andReturn($bill);

        $this->inventoryRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($expectedInventoryParams)
            ->andReturn($inventory);

        Log::shouldReceive('info')
            ->with('Item has been successfully created', ['inventoryId' => $inventory->inventory_id]);

        $this->responseCacheKeyMock->shouldReceive('deleteByDealer')->with($inventory->dealer_id)->andReturn($expectedCacheKey);
        $this->inventoryResponseCacheMock->shouldReceive('forget')->with([$expectedCacheKey]);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertEquals($inventory, $result);
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     *
     * @param array $params
     * @throws BindingResolutionException
     * @throws InventoryException
     */
    public function testCreateWithoutInventory(array $params)
    {
        $params['new_files'] = [];

        $this->inventoryRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($params)
            ->andReturn(null);

        $this->inventoryRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->twice()
            ->with();

        Log::shouldReceive('error');

        $this->expectException(InventoryException::class);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertNull($result);
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     *
     * @param array $params
     * @throws BindingResolutionException
     * @throws InventoryException
     */
    public function testCreateWithException(array $params)
    {
        $exception = new \Exception();
        $params['new_files'] = [];

        $this->inventoryRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($params)
            ->andThrow($exception);

        $this->inventoryRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->once()
            ->with();

        Log::shouldReceive('error');

        $this->expectException(InventoryException::class);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertNull($result);
    }

    /**
     * @covers ::delete
     * @dataProvider deleteParamsProvider
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     *
     * @param $imageParams
     * @param $fileParams
     * @throws BindingResolutionException
     */
    public function testDeleteWithImagesAndFiles($imageParams, $fileParams)
    {
        $inventoryId = PHP_INT_MAX;
        $imageModels = new Collection();
        $fileModels = new Collection();

        $imageModel1 = $this->getEloquentMock(Image::class);
        $imageModel1->image_id = 1;
        $imageModel1->filename = 'test_' . 1;
        $imageModel1->inventory_images_count = 2;
        $imageModels->push($imageModel1);

        $imageModel2 = $this->getEloquentMock(Image::class);
        $imageModel2->image_id = 2;
        $imageModel2->filename = 'test_' . 2;
        $imageModel2->inventory_images_count = 1;
        $imageModels->push($imageModel2);

        $fileModel1 = $this->getEloquentMock(File::class);
        $fileModel1->id = 1;
        $fileModel1->path = 'test_' . 1;
        $fileModel1->inventory_files_count = 1;
        $fileModels->push($fileModel1);

        $fileModel2 = $this->getEloquentMock(File::class);
        $fileModel2->id = 2;
        $fileModel2->path = 'test_' . 2;
        $fileModel2->inventory_files_count = 2;
        $fileModels->push($fileModel2);

        $this->imageRepositoryMock
            ->shouldReceive('getAllByInventoryId')
            ->once()
            ->with($inventoryId, $imageParams)
            ->andReturn($imageModels);

        $inventoryDeleteParams = [
            'id' => $inventoryId,
            'imageIds' => [$imageModel2->image_id],
            'fileIds' => [$fileModel1->id],
        ];

        $this->doesntExpectJobs(DeleteS3FilesJob::class);

        $this->fileRepositoryMock
            ->shouldReceive('getAllByInventoryId')
            ->once()
            ->with($inventoryId, $fileParams)
            ->andReturn($fileModels);

        $this->inventoryRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with($inventoryDeleteParams)
            ->andReturn(true);

        $this->doesntExpectJobs(DeleteS3FilesJob::class);

        Log::shouldReceive('info')
            ->with('Item has been successfully deleted', ['inventoryId' => $inventoryId]);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->delete($inventoryId);

        $this->assertTrue($result);
    }

    /**
     * @covers ::delete
     * @dataProvider deleteParamsProvider
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     *
     * @param $imageParams
     * @param $fileParams
     * @throws BindingResolutionException
     */
    public function testDeleteWithoutImagesAndFiles($imageParams, $fileParams)
    {
        $inventoryId = PHP_INT_MAX;
        $emptyCollection = new Collection();

        $this->imageRepositoryMock
            ->shouldReceive('getAllByInventoryId')
            ->once()
            ->with($inventoryId, $imageParams)
            ->andReturn($emptyCollection);

        $this->imageRepositoryMock
            ->shouldReceive('delete')
            ->never();

        $this->fileRepositoryMock
            ->shouldReceive('getAllByInventoryId')
            ->once()
            ->with($inventoryId, $fileParams)
            ->andReturn($emptyCollection);

        $this->fileRepositoryMock
            ->shouldReceive('delete')
            ->never();

        $this->inventoryRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with(['id' => $inventoryId])
            ->andReturn(true);

        $this->doesntExpectJobs(DeleteS3FilesJob::class);

        Log::shouldReceive('info')
            ->with('Item has been successfully deleted', ['inventoryId' => $inventoryId]);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->delete($inventoryId);

        $this->assertTrue($result);
    }

    /**
     * @covers ::delete
     * @dataProvider deleteParamsProvider
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     *
     * @param $imageParams
     * @param $fileParams
     * @throws BindingResolutionException
     */
    public function testDeleteWithException($imageParams, $fileParams)
    {
        $inventoryId = PHP_INT_MAX;
        $exception = new \Exception();

        $this->imageRepositoryMock
            ->shouldReceive('getAllByInventoryId')
            ->once()
            ->with($inventoryId, $imageParams)
            ->andThrow($exception);

        $this->imageRepositoryMock
            ->shouldReceive('delete')
            ->never();

        $this->fileRepositoryMock
            ->shouldReceive('getAllByInventoryId')
            ->never();

        $this->fileRepositoryMock
            ->shouldReceive('delete')
            ->never();

        $this->inventoryRepositoryMock
            ->shouldReceive('delete')
            ->never();

        $this->doesntExpectJobs(DeleteS3FilesJob::class);

        Log::shouldReceive('error')
            ->with('Item delete error.', $exception->getTrace());

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->delete($inventoryId);

        $this->assertFalse($result);
    }

    /**
     * @covers ::deleteDuplicates
     * @dataProvider deleteDuplicatesParamsProvider
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group Marketing
     * @group Marketing_Overlays
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     *
     * @param array $getAllWithHavingCountParams
     * @param array $getAllParams
     */
    public function testDeleteDuplicates(array $getAllWithHavingCountParams, array $getAllParams)
    {
        $dealerId = self::TEST_DEALER_ID;

        $testStock1 = 'test_stock_' . 1;
        $testStock2 = 'test_stock_' . 2;
        $testStock3 = 'test_stock_' . 3;

        $stockArray = [$testStock1, $testStock2, $testStock3];
        $stockCollection = new Collection([['stock' => $testStock1], ['stock' => $testStock2], ['stock' => $testStock3]]);

        $getAllParams[Repository::CONDITION_AND_WHERE_IN] = ['stock' => $stockArray];

        $inventory1 = new \StdClass();
        $inventory1->inventory_id = 1;
        $inventory1->stock = $testStock1;
        $inventory1->updated_at = new \DateTime('now');
        $inventory1->created_at = new \DateTime('now');
        $inventory1->is_archived = true;

        $inventory2 = new \StdClass();
        $inventory2->inventory_id = 2;
        $inventory2->stock = $testStock1;
        $inventory2->updated_at = new \DateTime('yesterday');
        $inventory2->created_at = new \DateTime('yesterday');
        $inventory2->is_archived = false;

        $inventory3 = new \StdClass();
        $inventory3->inventory_id = 3;
        $inventory3->stock = $testStock2;
        $inventory3->updated_at = new \DateTime('now');
        $inventory3->created_at = new \DateTime('-1 days');
        $inventory3->is_archived = false;

        $inventory4 = new \StdClass();
        $inventory4->inventory_id = 4;
        $inventory4->stock = $testStock2;
        $inventory4->updated_at = new \DateTime('yesterday');
        $inventory4->created_at = new \DateTime('yesterday');
        $inventory4->is_archived = false;

        $inventory5 = new \StdClass();
        $inventory5->inventory_id = 5;
        $inventory5->stock = $testStock2;
        $inventory5->updated_at = new \DateTime('-2 days');
        $inventory5->created_at = new \DateTime('yesterday');
        $inventory5->is_archived = false;

        $inventory6 = new \StdClass();
        $inventory6->inventory_id = 6;
        $inventory6->stock = $testStock3;
        $inventory6->updated_at = new \DateTime('-3 days');
        $inventory6->created_at = new \DateTime('-3 days');
        $inventory6->is_archived = false;

        $inventory7 = new \StdClass();
        $inventory7->inventory_id = 7;
        $inventory7->stock = $testStock3;
        $inventory7->updated_at = new \DateTime('-2 days');
        $inventory7->created_at = new \DateTime('-3 days');
        $inventory7->is_archived = false;

        $inventoryCollection = new Collection([$inventory1, $inventory2, $inventory3, $inventory4, $inventory5, $inventory6, $inventory7]);

        /** @var InventoryService|MockObject $inventoryServiceMock */
        $inventoryServiceMock = $this->getMockBuilder(InventoryService::class)
            ->setConstructorArgs($this->getInventoryServiceDependencies())
            ->onlyMethods(['delete'])
            ->getMock();

        $this->inventoryRepositoryMock
            ->shouldReceive('getAllWithHavingCount')
            ->once()
            ->with($getAllWithHavingCountParams, false)
            ->andReturn($stockCollection);

        $this->inventoryRepositoryMock
            ->shouldReceive('getAll')
            ->once()
            ->with($getAllParams, false)
            ->andReturn($inventoryCollection);

        $inventoryServiceMock
            ->expects($this->exactly(4))
            ->method('delete')
            ->willReturnOnConsecutiveCalls(true, true, true, false);

        $result = $inventoryServiceMock->deleteDuplicates($dealerId);
        $assertedResult = ['deletedDuplicates' => 3, 'couldNotDeleteDuplicates' => [6]];

        $this->assertSame($assertedResult, $result);
    }

    /**
     * @covers ::deleteDuplicates
     * @dataProvider deleteDuplicatesParamsProvider
     *
     * @group DMS
     * @group DMS_INVENTORY
     * @group Marketing
     * @group Marketing_Overlays
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     *
     * @param array $getAllWithHavingCountParams
     * @param array $getAllParams
     */
    public function testDeleteDuplicatesWithoutDuplicates(array $getAllWithHavingCountParams, array $getAllParams)
    {
        $dealerId = self::TEST_DEALER_ID;
        $emptyCollection = new Collection();

        /** @var InventoryService|LegacyMockInterface $inventoryServiceMock */
        $inventoryServiceMock = Mockery::mock(InventoryService::class, $this->getInventoryServiceDependencies());

        $inventoryServiceMock->shouldReceive('deleteDuplicates')->passthru();

        $this->inventoryRepositoryMock
            ->shouldReceive('getAllWithHavingCount')
            ->once()
            ->with($getAllWithHavingCountParams, false)
            ->andReturn($emptyCollection);

        $this->inventoryRepositoryMock
            ->shouldReceive('getAll')
            ->never();

        $inventoryServiceMock
            ->shouldReceive('delete')
            ->never();

        $result = $inventoryServiceMock->deleteDuplicates($dealerId);
        $assertedResult = ['deletedDuplicates' => 0, 'couldNotDeleteDuplicates' => []];

        $this->assertSame($assertedResult, $result);
    }

    /**
     * @group DMS
     * @group DMS_INVENTORY
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function testDeliveryPrice() {
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->id = 1;
        $inventory->latitude = 10;
        $inventory->longitude = 10;

        $dealerLocation = $this->getEloquentMock(DealerLocation::class);
        $dealerLocation->latitude = 11;
        $dealerLocation->longitude = 11;
        $dealerLocation->dealer_location_id = 1;
        $dealerLocation
            ->shouldReceive('getKey')
            ->once()
            ->andReturn(1);

        $inventory->dealerLocation = $dealerLocation;

        $mileageFee = $this->getEloquentMock(DealerLocationMileageFee::class);
        $mileageFee->fee_per_mile = 1;

        $inventoryCategory = $this->getEloquentMock(InventoryCategory::class);
        $inventoryCategory->id = 1;
        $inventoryCategory
            ->shouldReceive('getKey')
            ->once()
            ->andReturn(1);

        $this->inventoryRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->andReturn($inventory);
        $this->categoryRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->andReturn($inventoryCategory);
        $this->dealerLocationMileageFeeRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->andReturn($mileageFee);
        $inventoryService = app()->make(InventoryServiceInterface::class);
        $price = $inventoryService->deliveryPrice($inventory->id);
        $this->assertGreaterThan(96, $price);
        $this->assertLessThan(97, $price);
    }

    /**
     * @return \array[][]
     */
    public function createParamsProvider(): array
    {
        return [[
            [
                'dealer_id' => self::TEST_DEALER_ID,
                'vin' => self::TEST_VIN,
                'stock' => self::TEST_STOCK,
                'model' => self::TEST_MODEL,
                'title' => self::TEST_TITLE,
                'b_vendorId' => 123,
                'b_status' => 345,
                'b_docNum' => 789,
                'b_receivedDate' => '11.11.11',
                'b_dueDate' => '12.12.12',
                'b_memo' => 'b_memo',
                'b_id' => '555',
                'b_isFloorPlan' => true
            ]
        ]];
    }

    /**
     * @return \string[][][]
     */
    public function deleteParamsProvider(): array
    {
        return [[
            [Repository::RELATION_WITH_COUNT => 'inventoryImages'],
            [Repository::RELATION_WITH_COUNT => 'inventoryFiles']
        ]];
    }

    /**
     * @return \array[][]
     */
    public function deleteDuplicatesParamsProvider(): array
    {
        return [[
            [
                Repository::SELECT => ['stock'],
                Repository::CONDITION_AND_WHERE => [['dealer_id', '=', self::TEST_DEALER_ID]],
                Repository::CONDITION_AND_HAVING_COUNT => ['inventory_id', '>', 1],
                Repository::GROUP_BY => ['stock'],
            ],
            [
                Repository::CONDITION_AND_WHERE => [['dealer_id', '=', self::TEST_DEALER_ID]],
                Repository::CONDITION_AND_WHERE_IN => ['stock' => []],
            ]
        ]];
    }

    /**
     * @param Inventory $inventory
     * @param array $params
     * @return array
     */
    private function getBillInfo(Inventory $inventory, array $params): array
    {
        return [
            'dealer_id' => $inventory->dealer_id,
            'inventory_id' => $inventory->inventory_id,
            'vendor_id' => $params['b_vendorId'] ?? null,
            'status' => $params['b_status'] ?? null,
            'doc_num' => $params['b_docNum'] ?? null,
            'received_date' => $params['b_receivedDate'] ?? null,
            'due_date' => $params['b_dueDate'] ?? null,
            'memo' => $params['b_memo'] ?? null,
            'id' => $params['b_id'] ?? null,
            'is_floor_plan' => $params['b_isFloorPlan'] ?? 0
        ];
    }

    /**
     * @param Inventory $inventory
     * @param Bill $bill
     * @param array $billInfo
     * @return array
     */
    private function getBillInventoryUpdateParams(Inventory $inventory, Bill $bill, array $billInfo): array
    {
        return  [
            'inventory_id' => $inventory->inventory_id,
            'send_to_quickbooks' => 1,
            'bill_id' => $bill->id,
            'is_floorplan_bill' => $billInfo['is_floor_plan'],
            'qb_sync_processed' => 0,
        ];
    }

    /**
     * @return array[]
     */
    public function overlayParamDataProvider()
    {
        return [[[
            'dealer_id' => self::TEST_DEALER_ID,
            'inventory_id' => self::TEST_INVENTORY_ID,
            'overlay_logo' => 'logo.png',
            'overlay_logo_position' => User::OVERLAY_LOGO_POSITION_LOWER_RIGHT,
            'overlay_logo_width' => '20%',
            'overlay_logo_height' => '20%',
            'overlay_upper' => User::OVERLAY_UPPER_DEALER_NAME,
            'overlay_upper_bg' => '#000000',
            'overlay_upper_alpha' => 0,
            'overlay_upper_text' => '#ffffff',
            'overlay_upper_size' => 40,
            'overlay_upper_margin' => 40,
            'overlay_lower' => User::OVERLAY_UPPER_DEALER_PHONE,
            'overlay_lower_bg' => '#000000',
            'overlay_lower_alpha' => 0,
            'overlay_lower_text' => '#ffffff',
            'overlay_lower_size' => 40,
            'overlay_lower_margin' => 40,
            'overlay_enabled' => Inventory::OVERLAY_ENABLED_ALL,
            'dealer_overlay_enabled' => Inventory::OVERLAY_ENABLED_ALL,
            'overlay_text_dealer' => 'DEALER_NAME',
            'overlay_text_phone' => 'DEALER_PHONE_NUMBER',
            'overlay_text_location' => 'DEALER_LOCATION',
        ]]];
    }

    /**
     * Test if overlay_enabled = Inventory::OVERLAY_ENABLED_ALL
     *
     * @dataProvider overlayParamDataProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testGenerateOverlaysAll($overlayParams)
    {
        $overlayParams['dealer_overlay_enabled'] = Inventory::OVERLAY_ENABLED_PRIMARY;
        $overlayParams['overlay_enabled'] = Inventory::OVERLAY_ENABLED_ALL;
        
        $inventoryImages = new Collection();

        $image1 = $this->getEloquentMock(Image::class);
        $image1->image_id = 1;
        $image1->filename = 'filename_1';
        $image1->filename_noverlay = '';

        $inventoryImage1 = $this->getEloquentMock(InventoryImage::class);
        $inventoryImage1->image = $image1;
        $inventoryImage1->is_default = 0;
        $inventoryImage1->position = 1;
        $inventoryImages->push($inventoryImage1);

        // Mock Image with existing overlay
        $image2 = $this->getEloquentMock(Image::class);
        $image2->image_id = 2;
        $image2->filename = 'filename_with_overlay_2';
        $image2->filename_noverlay = 'filename_2';

        $inventoryImage2 = $this->getEloquentMock(InventoryImage::class);
        $inventoryImage2->image = $image2;
        $inventoryImage2->is_default = 0;
        $inventoryImage2->position = 2;
        $inventoryImages->push($inventoryImage2);

        $this->inventoryRepositoryMock
            ->shouldReceive('getOverlayParams')
            ->with(self::TEST_INVENTORY_ID)
            ->once()
            ->andReturn($overlayParams);

        $this->inventoryRepositoryMock
            ->shouldReceive('getInventoryImages')
            ->with(self::TEST_INVENTORY_ID)
            ->once()
            ->andReturn($inventoryImages);

        foreach ($inventoryImages as $inventoryImage) {

            $image = $inventoryImage->image;
            $imageId = $image->image_id;
            $s3Filename = 's3_image_with_overlay_'. $imageId;
            $tmpFilename = 'tmp_image_with_overlay_'. $imageId;

            // Mock uploadToS3
            $this->imageServiceMock
                ->shouldReceive('uploadToS3')
                ->once()->andReturn($s3Filename);

            // Mock addOverlays
            Storage::disk('tmp')->put($tmpFilename, '');
            $this->imageServiceMock
                ->shouldReceive('addOverlays')
                ->once()->andReturn(
                    Storage::disk('tmp')->path($tmpFilename)
                );

            // Mock saveOverlay
            $this->imageTableServiceMock
                ->shouldReceive('saveOverlay')
                ->once()->with($image, $s3Filename);
        }

        $this->imageTableServiceMock->shouldNotReceive('resetOverlay');

        $inventoryServiceMock = Mockery::mock(InventoryService::class, $this->getInventoryServiceDependencies())->makePartial();

        $inventoryServiceMock->generateOverlays(self::TEST_INVENTORY_ID);

        Storage::disk('tmp')->assertMissing([
            'tmp_image_with_overlay_1',
            'tmp_image_with_overlay_2'
        ]);
    }

    /**
     * Test if overlay_enabled = Inventory::OVERLAY_ENABLED_PRIMARY
     *
     * @dataProvider overlayParamDataProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testGenerateOverlaysPrimary($overlayParams)
    {
        $overlayParams['dealer_overlay_enabled'] = Inventory::OVERLAY_ENABLED_ALL;
        $overlayParams['overlay_enabled'] = Inventory::OVERLAY_ENABLED_PRIMARY;

        $inventoryImages = new Collection();

        $image1 = $this->getEloquentMock(Image::class);
        $image1->image_id = 1;
        $image1->filename = 'filename_1';

        $inventoryImage1 = $this->getEloquentMock(InventoryImage::class);
        $inventoryImage1->image = $image1;
        $inventoryImage1->is_default = 0;
        $inventoryImage1->position = 1;
        $inventoryImages->push($inventoryImage1);

        $image2 = $this->getEloquentMock(Image::class);
        $image2->image_id = 2;
        $image2->filename = 'filename_2';

        $inventoryImage2 = $this->getEloquentMock(InventoryImage::class);
        $inventoryImage2->image = $image2;
        $inventoryImage2->is_default = 0;
        $inventoryImage2->position = 2;
        $inventoryImages->push($inventoryImage2);

        $this->inventoryRepositoryMock
            ->shouldReceive('getOverlayParams')
            ->with(self::TEST_INVENTORY_ID)
            ->once()
            ->andReturn($overlayParams);

        $this->inventoryRepositoryMock
            ->shouldReceive('getInventoryImages')
            ->with(self::TEST_INVENTORY_ID)
            ->once()
            ->andReturn($inventoryImages);

        foreach ($inventoryImages as $inventoryImage) {

            $image = $inventoryImage->image;
            $imageId = $image->image_id;
            $s3Filename = 's3_image_with_overlay_'. $imageId;
            $tmpFilename = 'tmp_image_with_overlay_'. $imageId;

            if ($imageId == 1) {

                // Mock uploadToS3
                $this->imageServiceMock
                    ->shouldReceive('uploadToS3')
                    ->once()->andReturn($s3Filename);

                // Mock addOverlays
                Storage::disk('tmp')->put($tmpFilename, '');
                $this->imageServiceMock
                    ->shouldReceive('addOverlays')
                    ->once()->andReturn(Storage::disk('tmp')->path($tmpFilename));

                // Mock saveOverlay
                $this->imageTableServiceMock
                    ->shouldReceive('saveOverlay')
                    ->once()->with($image, $s3Filename);

            } else {

                // Mock resetOverlay
                $this->imageTableServiceMock
                    ->shouldReceive('resetOverlay')
                    ->once()->withArgs([$image]);

                $this->imageTableServiceMock
                    ->shouldNotReceive('saveOverlay')->with($image);
            }
        }

        $inventoryServiceMock = Mockery::mock(InventoryService::class, $this->getInventoryServiceDependencies())->makePartial();

        $inventoryServiceMock->generateOverlays(self::TEST_INVENTORY_ID);

        Storage::disk('tmp')->assertMissing([
            'tmp_image_with_overlay_1'
        ]);
    }

    /**
     * Test if addOverlays() is null
     *
     * @dataProvider overlayParamDataProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testGenerateOverlaysWithNoOverlay($overlayParams)
    {
        $inventoryImages = new Collection();

        $image1 = $this->getEloquentMock(Image::class);
        $image1->image_id = 1;
        $image1->filename = 'filename_1';
        $image1->filename_noverlay = '';

        $inventoryImage1 = $this->getEloquentMock(InventoryImage::class);
        $inventoryImage1->image = $image1;
        $inventoryImage1->is_default = 0;
        $inventoryImage1->position = 1;
        $inventoryImages->push($inventoryImage1);

        // Mock Image with existing overlay
        $image2 = $this->getEloquentMock(Image::class);
        $image2->image_id = 2;
        $image2->filename = 'filename_with_overlay_2';
        $image2->filename_noverlay = 'filename_2';

        $inventoryImage2 = $this->getEloquentMock(InventoryImage::class);
        $inventoryImage2->image = $image2;
        $inventoryImage2->is_default = 0;
        $inventoryImage2->position = 2;
        $inventoryImages->push($inventoryImage2);

        $this->inventoryRepositoryMock
            ->shouldReceive('getOverlayParams')
            ->with(self::TEST_INVENTORY_ID)
            ->once()
            ->andReturn($overlayParams);

        $this->inventoryRepositoryMock
            ->shouldReceive('getInventoryImages')
            ->with(self::TEST_INVENTORY_ID)
            ->once()
            ->andReturn($inventoryImages);

        foreach ($inventoryImages as $inventoryImage) {

            // Mock addOverlays
            $this->imageServiceMock
                ->shouldReceive('addOverlays')
                ->once()
                ->andReturn(null);
        }

        $this->imageTableServiceMock->shouldNotReceive('saveOverlay');
        $this->imageServiceMock->shouldNotReceive('uploadToS3');
        $this->imageTableServiceMock->shouldNotReceive('resetOverlay');

        $inventoryServiceMock = Mockery::mock(InventoryService::class, $this->getInventoryServiceDependencies())->makePartial();

        $inventoryServiceMock->generateOverlays(self::TEST_INVENTORY_ID);

        Storage::disk('tmp')->assertMissing([
            'tmp_image_with_overlay_1',
            'tmp_image_with_overlay_2'
        ]);
    }

    /**
     * Test if missing inventoryImages
     *
     * @dataProvider overlayParamDataProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testGenerateOverlaysNone($overlayParams)
    {
        $inventoryImages = new Collection();

        $this->inventoryRepositoryMock
            ->shouldReceive('getInventoryImages')
            ->with(self::TEST_INVENTORY_ID)
            ->once()
            ->andReturn($inventoryImages);

        Log::shouldReceive('info')->never();

        $this->inventoryRepositoryMock
            ->shouldNotReceive('getOverlayParams');

        $this->imageServiceMock->shouldNotReceive('uploadToS3');
        $this->imageServiceMock->shouldNotReceive('addOverlays');
        $this->imageTableServiceMock->shouldNotReceive('saveOverlay');
        $this->imageTableServiceMock->shouldNotReceive('resetOverlay');

        $inventoryServiceMock = Mockery::mock(InventoryService::class, $this->getInventoryServiceDependencies())->makePartial();

        $inventoryServiceMock->generateOverlays(self::TEST_INVENTORY_ID);
    }

    /**
     * Test if overlay_enabled = 0
     *
     * @dataProvider overlayParamDataProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testResetOverlays($overlayParams)
    {
        $overlayParams['dealer_overlay_enabled'] = Inventory::OVERLAY_ENABLED_ALL;
        $overlayParams['overlay_enabled'] = 0;

        $inventoryImages = new Collection();

        $image1 = $this->getEloquentMock(Image::class);
        $image1->filename = 'filename_1';

        $inventoryImage1 = $this->getEloquentMock(InventoryImage::class);
        $inventoryImage1->image = $image1;
        $inventoryImage1->is_default = 0;
        $inventoryImage1->position = 1;
        $inventoryImages->push($inventoryImage1);

        // Mock Image with existing overlay
        $image2 = $this->getEloquentMock(Image::class);
        $image2->filename = 'filename_with_overlay_2';
        $image2->filename_noverlay = 'filename_2';

        $inventoryImage2 = $this->getEloquentMock(InventoryImage::class);
        $inventoryImage2->image = $image2;
        $inventoryImage2->is_default = 0;
        $inventoryImage2->position = 2;
        $inventoryImages->push($inventoryImage2);

        $this->inventoryRepositoryMock
            ->shouldReceive('getOverlayParams')
            ->with(self::TEST_INVENTORY_ID)
            ->once()
            ->andReturn($overlayParams);

        $this->inventoryRepositoryMock
            ->shouldReceive('getInventoryImages')
            ->with(self::TEST_INVENTORY_ID)
            ->once()
            ->andReturn($inventoryImages);

        $this->imageTableServiceMock->shouldNotReceive('saveOverlay');
        $this->imageServiceMock->shouldNotReceive('uploadToS3');
        $this->imageServiceMock->shouldNotReceive('addOverlays');

        foreach ($inventoryImages as $inventoryImage) {
            $image = $inventoryImage->image;

            $this->imageTableServiceMock->shouldReceive('resetOverlay')
                ->once()->with($image);
        }

        $inventoryServiceMock = Mockery::mock(InventoryService::class, $this->getInventoryServiceDependencies())->makePartial();

        $inventoryServiceMock->generateOverlays(self::TEST_INVENTORY_ID);
    }

    /**
     * @dataProvider createParamsProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testOverlayJobOnCreateWithoutImages($params)
    {
        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;
        $inventory->dealer_id = $this->faker->numberBetween(1222, 3333);
        $inventory->shouldReceive('searchable');

        $expectedCacheKey = sprintf('inventories.search.*.dealers:*_%d_*.inventories:*', $inventory->dealer_id);

        $this->inventoryRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->withAnyArgs()
            ->andReturn($inventory);

        $this->inventoryRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        Log::shouldReceive('info')
            ->with('Item has been successfully created', ['inventoryId' => self::TEST_INVENTORY_ID]);

        Log::shouldReceive('error')->never();

        $this->responseCacheKeyMock->shouldReceive('deleteByDealer')->with($inventory->dealer_id)->andReturn($expectedCacheKey);
        $this->inventoryResponseCacheMock->shouldReceive('forget')->with([$expectedCacheKey]);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertEquals($inventory, $result);

        Queue::assertNotPushed(GenerateOverlayImageJob::class);
    }

    /**
     * @dataProvider createParamsProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testOverlayJobOnCreateWithImages($params)
    {
        $params['new_images'] = ['tmp_image_path'];
        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;
        $inventory->dealer_id = $this->faker->numberBetween(1222, 3333);
        $inventory->shouldReceive('searchable');


        $expectedCacheKey = sprintf('inventories.search.*.dealers:*_%d_*.inventories:*', $inventory->dealer_id);

        $this->inventoryRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->withAnyArgs()
            ->andReturn($inventory);

        $this->inventoryRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        Log::shouldReceive('info')
            ->with('Item has been successfully created', ['inventoryId' => self::TEST_INVENTORY_ID]);

        Log::shouldReceive('error')->never();

        /** @var InventoryService $service */
        $inventoryServiceMock = Mockery::mock(InventoryService::class, $this->getInventoryServiceDependencies())->makePartial();

        $inventoryServiceMock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('uploadImages')
            ->once();

        $this->responseCacheKeyMock->shouldReceive('deleteByDealer')->with($inventory->dealer_id)->andReturn($expectedCacheKey);
        $this->inventoryResponseCacheMock->shouldReceive('forget')->with([$expectedCacheKey]);

        $result = $inventoryServiceMock->create($params);

        $this->assertEquals($inventory, $result);

        Queue::assertPushed(GenerateOverlayImageJob::class, 1);
        // sadly with this kinda mocking we can not test dispatched jobs via observers,
        // so we can not test indexation and invalidation dispatching jobs
    }

    /**
     * @dataProvider createParamsProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testOverlayJobOnUpdateWithoutImages($params)
    {
        $params['inventory_id'] = self::TEST_INVENTORY_ID;
        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;
        $inventory->dealer_id = $this->faker->numberBetween(1222, 3333);
        $inventory->wasRecentlyCreated = false;
        $inventory->shouldReceive('searchable');

        $expectedSearchCacheKey = sprintf('inventories.search.*.dealers:*_%d_*.inventories:*', $inventory->dealer_id);
        $expectedSingleCacheKey = sprintf('inventories.single.%d.dealer:%d',$inventory->inventory_id, $inventory->dealer_id);

        $this->inventoryRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->withAnyArgs()
            ->andReturn($inventory);

        $this->inventoryRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        $this->responseCacheKeyMock->shouldReceive('deleteByDealer')->with($inventory->dealer_id)->andReturn($expectedSearchCacheKey);
        $this->responseCacheKeyMock->shouldReceive('deleteSingle')->with($inventory->inventory_id, $inventory->dealer_id)->andReturn($expectedSingleCacheKey);
        $this->inventoryResponseCacheMock->shouldReceive('forget')->with([$expectedSearchCacheKey, $expectedSingleCacheKey]);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->update($params);

        $this->assertEquals($inventory, $result);

        Queue::assertNotPushed(GenerateOverlayImageJob::class);
    }

    /**
     * @dataProvider createParamsProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testOverlayJobOnUpdateWithNewImages($params)
    {
        $params['new_images'] = ['uploaded_img_path'];
        $params['inventory_id'] = self::TEST_INVENTORY_ID;
        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;
        $inventory->dealer_id = $this->faker->numberBetween(1222, 3333);
        $inventory->wasRecentlyCreated = false;
        $inventory->shouldReceive('searchable');
        $inventory->shouldReceive('jsonSerialize');

        $expectedSearchCacheKey = sprintf('inventories.search.*.dealers:*_%d_*.inventories:*', $inventory->dealer_id);
        $expectedSingleCacheKey = sprintf('inventories.single.%d.dealer:%d',$inventory->inventory_id, $inventory->dealer_id);

        $this->inventoryRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->withAnyArgs()
            ->andReturn($inventory);

        $this->inventoryRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        /** @var InventoryService $service */
        $inventoryServiceMock = Mockery::mock(InventoryService::class, $this->getInventoryServiceDependencies())->makePartial();

        $inventoryServiceMock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('uploadImages')
            ->once();

        $this->responseCacheKeyMock->shouldReceive('deleteByDealer')->with($inventory->dealer_id)->andReturn($expectedSearchCacheKey);
        $this->responseCacheKeyMock->shouldReceive('deleteSingle')->with($inventory->inventory_id, $inventory->dealer_id)->andReturn($expectedSingleCacheKey);
        $this->inventoryResponseCacheMock->shouldReceive('forget')->with([$expectedSearchCacheKey, $expectedSingleCacheKey]);

        $result = $inventoryServiceMock->update($params);

        $this->assertEquals($inventory, $result);

        Queue::assertPushed(GenerateOverlayImageJob::class, 1);
        // sadly with this kinda mocking we can not test dispatched jobs via observers,
        // so we can not test indexation and invalidation dispatching jobs
    }

    /**
     * @dataProvider createParamsProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testOverlayJobOnUpdateWithExistingImages($params)
    {
        Inventory::enableCacheInvalidation();

        $params['existing_images'] = ['uploaded_img_path'];
        $params['inventory_id'] = self::TEST_INVENTORY_ID;
        $params['title'] = 'some title';
        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;
        $inventory->dealer_id = $this->faker->numberBetween(1222, 3333);
        $inventory->wasRecentlyCreated = false;
        $inventory->shouldReceive('searchable');
        $inventory->shouldReceive('jsonSerialize');

        $expectedSearchCacheKey = sprintf('inventories.search.*.dealers:*_%d_*.inventories:*', $inventory->dealer_id);
        $expectedSingleCacheKey = sprintf('inventories.single.%d.dealer:%d',$inventory->inventory_id, $inventory->dealer_id);

        $this->inventoryRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->withAnyArgs()
            ->andReturn($inventory);

        $this->inventoryRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        $this->responseCacheKeyMock->shouldReceive('deleteByDealer')->with($inventory->dealer_id)->andReturn($expectedSearchCacheKey);
        $this->responseCacheKeyMock->shouldReceive('deleteSingle')->with($inventory->inventory_id, $inventory->dealer_id)->andReturn($expectedSingleCacheKey);
        $this->inventoryResponseCacheMock->shouldReceive('forget')->with([$expectedSearchCacheKey, $expectedSingleCacheKey]);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->update($params);

        $this->assertEquals($inventory, $result);

        Queue::assertPushed(GenerateOverlayImageJob::class, 1);
        // sadly with this kinda mocking we can not test dispatched jobs via observers,
        // so we can not test indexation and invalidation dispatching jobs
    }

    /**
     * @dataProvider createParamsProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testOverlayJobOnUpdateWithAnyImages($params)
    {
        $params['existing_images'] = ['uploaded_img_path'];
        $params['new_images'] = ['uploaded_img_path'];
        $params['inventory_id'] = self::TEST_INVENTORY_ID;
        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;
        $inventory->dealer_id = $this->faker->numberBetween(1222, 3333);
        $inventory->wasRecentlyCreated = false;
        $inventory->shouldReceive('searchable');

        $expectedSearchCacheKey = sprintf('inventories.search.*.dealers:*_%d_*.inventories:*', $inventory->dealer_id);
        $expectedSingleCacheKey = sprintf('inventories.single.%d.dealer:%d',$inventory->inventory_id, $inventory->dealer_id);

        $this->inventoryRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->withAnyArgs()
            ->andReturn($inventory);

        $this->inventoryRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->with();

        $this->inventoryRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        /** @var InventoryService $service */
        $inventoryServiceMock = Mockery::mock(InventoryService::class, $this->getInventoryServiceDependencies())->makePartial();

        $inventoryServiceMock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('uploadImages')
            ->once();

        $this->responseCacheKeyMock->shouldReceive('deleteByDealer')->with($inventory->dealer_id)->andReturn($expectedSearchCacheKey);
        $this->responseCacheKeyMock->shouldReceive('deleteSingle')->with($inventory->inventory_id, $inventory->dealer_id)->andReturn($expectedSingleCacheKey);
        $this->inventoryResponseCacheMock->shouldReceive('forget')->with([$expectedSearchCacheKey, $expectedSingleCacheKey]);

        $result = $inventoryServiceMock->update($params);

        $this->assertEquals($inventory, $result);

        Queue::assertPushed(GenerateOverlayImageJob::class, 1);
        // sadly with this kinda mocking we can not test dispatched jobs via observers,
        // so we can not test indexation and invalidation dispatching jobs
    }

    protected function getInventoryServiceDependencies(): array
    {
        return [
            $this->inventoryRepositoryMock,
            $this->imageRepositoryMock,
            $this->fileRepositoryMock,
            $this->billRepositoryMock,
            $this->quickbookApprovalRepositoryMock,
            $this->websiteConfigRepositoryMock,
            $this->imageServiceMock,
            $this->fileServiceMock,
            $this->dealerLocationRepositoryMock,
            $this->dealerLocationMileageFeeRepositoryMock,
            $this->categoryRepositoryMock,
            $this->imageTableServiceMock,
            $this->responseCacheKeyMock,
            $this->geolocationServiceMock,
            $this->inventoryResponseCacheMock,
            $this->logServiceMock ?? app()->make(LoggerServiceInterface::class)
        ];
    }
}
