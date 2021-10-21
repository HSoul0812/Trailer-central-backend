<?php

namespace Tests\Unit\Services\Inventory;

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
use App\Services\File\DTOs\FileDto;
use App\Services\File\FileService;
use App\Services\File\ImageService;
use App\Services\Inventory\InventoryService;
use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Test for App\Services\Inventory\InventoryService
 *
 * Class InventoryServiceTest
 * @package Tests\Unit\Services\Inventory
 *
 * @coversDefaultClass \App\Services\Inventory\InventoryService
 */
class InventoryServiceTest extends TestCase
{
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
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @param array $params
     * @throws BindingResolutionException
     */
    public function testCreateWithoutFilesAndBill(array $params)
    {
        $params['new_files'] = [];

        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;

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

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertEquals($inventory, $result);
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @param array $params
     * @throws BindingResolutionException
     */
    public function testCreateWithImages(array $params)
    {
        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;

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

        $overlayEnabledParams = ['overlayText' => $params['stock']];

        $this->inventoryRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->with();

        $expectedParams = $params;

        foreach ($params['new_images'] as $key => $image) {
            $newImage = new FileDto('path' . $key, 'hash' . $key);
            $newImageWithOverlay = new FileDto('path_with_overlay' . $key, 'hash_with_overlay' . $key);

            $this->imageServiceMock
                ->shouldReceive('upload')
                ->once()
                ->with($image['url'], $params['title'], self::TEST_DEALER_ID)
                ->andReturn($newImage);

            if ($image['position'] == 0) {
                $this->imageServiceMock
                    ->shouldReceive('upload')
                    ->once()
                    ->with($image['url'], $params['title'], self::TEST_DEALER_ID, null, $overlayEnabledParams)
                    ->andReturn($newImageWithOverlay);
            }

            if ($image['position'] == 0) {
                $expectedParams['new_images'][$key]['filename'] = $newImageWithOverlay->getPath();
                $expectedParams['new_images'][$key]['filename_noverlay'] = $newImage->getPath();
                $expectedParams['new_images'][$key]['hash'] = $newImageWithOverlay->getHash();
            } else {
                $expectedParams['new_images'][$key]['filename'] = $newImage->getPath();
                $expectedParams['new_images'][$key]['filename_noverlay'] = '';
                $expectedParams['new_images'][$key]['hash'] = $newImage->getHash();
            }
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

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertEquals($inventory, $result);
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @param array $params
     * @throws BindingResolutionException
     */
    public function testCreateWithFiles(array $params)
    {
        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;

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

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertEquals($inventory, $result);
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @param array $params
     * @throws BindingResolutionException
     */
    public function testCreateWithNewBillVendorAndBillExists(array $params)
    {
        $params['new_files'] = [];

        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;
        $inventory->dealer_id = self::TEST_DEALER_ID;

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
            ->with($bill->id);

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

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertEquals($inventory, $result);
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @param array $params
     * @throws BindingResolutionException
     */
    public function testCreateWithNewBillVendorAndBillNotExists(array $params)
    {
        $params['new_files'] = [];
        unset($params['b_id']);

        /** @var Inventory|LegacyMockInterface $inventory */
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->inventory_id = self::TEST_INVENTORY_ID;
        $inventory->dealer_id = self::TEST_DEALER_ID;

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

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertEquals($inventory, $result);
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @param array $params
     * @throws BindingResolutionException
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

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertEquals($inventory, $result);
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @param array $params
     * @throws BindingResolutionException
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
            ->once()
            ->with();

        Log::shouldReceive('error')
            ->with('Item hasn\'t been created.', ['params' => $params]);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertNull($result);
    }

    /**
     * @covers ::create
     * @dataProvider createParamsProvider
     *
     * @param array $params
     * @throws BindingResolutionException
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

        Log::shouldReceive('error')
            ->with('Item create error. Params - ' . json_encode($params), $exception->getTrace());

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->create($params);

        $this->assertNull($result);
    }

    /**
     * @covers ::delete
     * @dataProvider deleteParamsProvider
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

        $this->expectsJobs(DeleteS3FilesJob::class);

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

        $this->expectsJobs(DeleteS3FilesJob::class);

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
            ->setConstructorArgs([
                $this->inventoryRepositoryMock,
                $this->imageRepositoryMock,
                $this->fileRepositoryMock,
                $this->billRepositoryMock,
                $this->quickbookApprovalRepositoryMock,
                $this->imageServiceMock,
                $this->fileServiceMock,
                $this->dealerLocationRepositoryMock,
                $this->dealerLocationMileageFeeRepositoryMock,
                $this->categoryRepositoryMock
            ])
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
            ->expects($this->at(0))
            ->method('delete')
            ->with($this->equalTo(1))
            ->willReturn(true);

        $inventoryServiceMock
            ->expects($this->at(1))
            ->method('delete')
            ->with($this->equalTo(4))
            ->willReturn(true);

        $inventoryServiceMock
            ->expects($this->at(2))
            ->method('delete')
            ->with($this->equalTo(5))
            ->willReturn(true);

        $inventoryServiceMock
            ->expects($this->at(3))
            ->method('delete')
            ->with($this->equalTo(6))
            ->willReturn(false);

        $result = $inventoryServiceMock->deleteDuplicates($dealerId);
        $assertedResult = ['deletedDuplicates' => 3, 'couldNotDeleteDuplicates' => [6]];

        $this->assertSame($assertedResult, $result);
    }

    /**
     * @covers ::deleteDuplicates
     * @dataProvider deleteDuplicatesParamsProvider
     *
     * @param array $getAllWithHavingCountParams
     * @param array $getAllParams
     */
    public function testDeleteDuplicatesWithoutDuplicates(array $getAllWithHavingCountParams, array $getAllParams)
    {
        $dealerId = self::TEST_DEALER_ID;
        $emptyCollection = new Collection();

        /** @var InventoryService|LegacyMockInterface $inventoryServiceMock */
        $inventoryServiceMock = Mockery::mock(InventoryService::class, [
            $this->inventoryRepositoryMock,
            $this->imageRepositoryMock,
            $this->fileRepositoryMock,
            $this->billRepositoryMock,
            $this->quickbookApprovalRepositoryMock,
            $this->imageServiceMock,
            $this->fileServiceMock
        ]);

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

    public function testDeliveryPrice() {
        $inventory = $this->getEloquentMock(Inventory::class);
        $inventory->id = 1;
        $inventory->lat = 10;
        $inventory->lng = 10;

        $dealerLocation = $this->getEloquentMock(DealerLocation::class);
        $dealerLocation->lat = 11;
        $dealerLocation->lng = 11;
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
}
