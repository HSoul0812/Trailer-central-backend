<?php

namespace Tests\Unit\Services\Inventory;

use App\Jobs\Inventory\GenerateOverlayImageJobByDealer;
use App\Jobs\Inventory\ReIndexInventoriesByDealersJob;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;
use App\Jobs\Inventory\GenerateOverlayImageJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use App\Models\User\User;
use App\Models\Inventory\Inventory;
use App\Services\Inventory\ImageServiceInterface;
use App\Services\Inventory\ImageService;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Models\Inventory\Image;
use Illuminate\Support\Collection;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use Mockery\LegacyMockInterface;

/**
 * Test for App\Services\Inventory\ImageService
 *
 * Class ImageServiceTest
 * @package Tests\Unit\Services\Inventory
 *
 * @group DW
 * @group DW_ELASTICSEARCH
 * @group DW_INVENTORY
 *
 * @coversDefaultClass \App\Services\Inventory\ImageService
 */
class ImageServiceTest extends TestCase
{
    use WithFaker;

    const DEALER_ID = 1;
    /**
     * @var LegacyMockInterface|ImageRepositoryInterface
     */
    private $imageRepositoryMock;

    /**
     * @var LegacyMockInterface|UserRepositoryInterface
     */
    private $userRepositoryMock;

    /**
     * @var LegacyMockInterface|ImageServiceInterface
     */
    private $imageService;

    /**
     * @var LegacyMockInterface|InventoryRepositoryInterface
     */
    private $inventoryRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->imageRepositoryMock = Mockery::mock(ImageRepositoryInterface::class);
        $this->app->instance(ImageRepositoryInterface::class, $this->imageRepositoryMock);

        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepositoryMock);

        $this->inventoryRepositoryMock = Mockery::mock(InventoryRepositoryInterface::class);
        $this->app->instance(InventoryRepositoryInterface::class, $this->inventoryRepositoryMock);

        $this->imageService = $this->getMockBuilder(ImageService::class)
            ->setConstructorArgs([
                $this->imageRepositoryMock,
                $this->userRepositoryMock,
                $this->inventoryRepositoryMock
            ])
            ->onlyMethods(['getFileHash'])
            ->getMock();

        Storage::fake('s3');
        Queue::fake();

    }

    public function tearDown(): void
    {
        Storage::fake('s3');

        parent::tearDown();
    }

    /**
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testGetFileHash()
    {
        $this->assertTrue(true);
    }

    /**
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testSaveOverlayWithEmptyNoverlay()
    {
        Storage::disk('s3')->put('image_1', '');
        Storage::disk('s3')->put('image_with_overlay_1', '');

        /** @var Image|LegacyMockInterface $image1 */
        $image1 = $this->getEloquentMock(Image::class);
        $image1->image_id = 1;
        $image1->filename = 'image_1';
        $image1->filename_noverlay = '';

        $this->imageRepositoryMock->shouldReceive('update')
            ->once()
            ->with([
                'filename' => 'image_with_overlay_1',
                'filename_with_overlay' => 'image_with_overlay_1',
                'hash' => 'test_hash',
                'id' => 1
            ]);

        $this->imageService->expects($this->once())
            ->method('getFileHash')
            ->willReturn('test_hash');

        $this->inventoryRepositoryMock->shouldReceive('markImageAsOverlayGenerated')
            ->once()
            ->with($image1->image_id);

        $this->imageRepositoryMock->shouldReceive('scheduleObjectToBeDroppedByURL')
            ->once()
            ->with($image1->filename);

        $this->imageService->saveOverlay($image1, 'image_with_overlay_1');

        Storage::disk('s3')->assertExists([
            'image_with_overlay_1',
            'image_1'
        ]);
    }

    /**
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testSaveOverlayWithExistingFilename()
    {
        Storage::disk('s3')->put('image_1', '');
        Storage::disk('s3')->put('image_with_overlay_1', '');
        Storage::disk('s3')->put('new_image_with_overlay_1', '');

        $image1 = $this->getEloquentMock(Image::class);
        $image1->image_id = 1;
        $image1->filename = 'image_with_overlay_1';
        $image1->filename_noverlay = 'image_1';

        $this->imageRepositoryMock->shouldReceive('update')
            ->once()
            ->with([
                'filename' => 'new_image_with_overlay_1',
                'filename_noverlay' => 'image_1',
                'hash' => 'test_hash',
                'id' => 1
            ]);

        $this->imageService->expects($this->once())
            ->method('getFileHash')
            ->willReturn('test_hash');

        $this->imageService->saveOverlay($image1, 'new_image_with_overlay_1');

        Storage::disk('s3')->assertExists([
            'new_image_with_overlay_1',
            'image_1'
        ]);

        /*
         * // due we have avoided to remove from S3 bucket because it is causing ES6 have broken images
         Storage::disk('s3')->assertMissing([
            'image_with_overlay_1'
        ]);
        */
    }

    /**
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testResetOverlayWithEmptyNoverlay()
    {
        Storage::disk('s3')->put('image_1', '');

        $image1 = $this->getEloquentMock(Image::class);
        $image1->image_id = 1;
        $image1->filename = 'image_1';
        $image1->filename_noverlay = '';

        $this->imageService->expects($this->exactly(0))
            ->method('getFileHash');

        $this->imageRepositoryMock->shouldNotReceive('update');

        $this->imageService->tryToRestoreOriginalImage($image1);

        Storage::disk('s3')->assertExists([
            'image_1'
        ]);
    }

    /**
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testResetOverlayWithExistingFilename()
    {
        Storage::disk('s3')->put('image_1', '');
        Storage::disk('s3')->put('image_with_overlay_1', '');

        $image1 = $this->getEloquentMock(Image::class);
        $image1->image_id = 1;
        $image1->filename = 'image_with_overlay_1';
        $image1->filename_noverlay = 'image_1';

        $this->imageRepositoryMock->shouldReceive('update')
            ->once()
            ->with([
                'filename' => 'image_1',
                'filename_noverlay' => '',
                'hash' => 'test_hash',
                'id' => 1
            ]);

        $this->imageService->expects($this->once())
            ->method('getFileHash')
            ->willReturn('test_hash');

        $this->imageService->tryToRestoreOriginalImage($image1);

        Storage::disk('s3')->assertExists([
            'image_1'
        ]);

        // due we have avoided to remove from S3 bucket because it is causing ES6 have broken images
        /*
         Storage::disk('s3')->assertMissing([
            'image_with_overlay_1'
        ]);
        */
    }

    /**
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testUpdateOverlaySettings()
    {
        $overlayParams = [
            'dealer_id' => self::DEALER_ID,
            'overlay_logo' => 'logo.png'
        ];

        $userMock = $this->getEloquentMock(User::class);
        $userMock->dealer_id = self::DEALER_ID;

        $inventories = new Collection();
        for ($i = 0; $i < 5; $i++)
        {
            $inventoryId = $i + 1;
            $inventory = $this->getEloquentMock(Inventory::class);
            $inventory->inventory_id = $inventoryId;
            $inventories->push($inventory);
        }

        $this->inventoryRepositoryMock->shouldNotReceive('massUpdate');

        $this->userRepositoryMock->shouldReceive('updateOverlaySettings')
            ->once()->with(self::DEALER_ID, $overlayParams)
            ->andReturn($overlayParams);

        $this->userRepositoryMock->shouldReceive('get')
            ->once()->with(['dealer_id' => self::DEALER_ID])
            ->andReturn($userMock);

        $this->imageService->updateOverlaySettings($overlayParams);

        Queue::assertNotPushed(GenerateOverlayImageJob::class);
        Queue::assertPushed(GenerateOverlayImageJobByDealer::class, 1);
    }

    /**
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testUpdateOverlaySettingsWithOverlayenabledChanged()
    {
        $overlayParams = [
            'dealer_id' => self::DEALER_ID,
            'overlay_logo' => 'logo.png',
            'overlay_enabled' => 2
        ];

        $userMock = $this->getEloquentMock(User::class);
        $userMock->dealer_id = self::DEALER_ID;

        $inventories = new Collection();
        for ($i = 0; $i < 5; $i++)
        {
            $inventoryId = $i + 1;
            $inventory = $this->getEloquentMock(Inventory::class);
            $inventory->inventory_id = $inventoryId;
            $inventories->push($inventory);
        }

        $this->inventoryRepositoryMock->shouldReceive('massUpdate')
            ->with([
                'dealer_id' => $overlayParams['dealer_id'],
                'overlay_enabled' => $overlayParams['overlay_enabled']
            ])
            ->once();

        $this->userRepositoryMock->shouldReceive('updateOverlaySettings')
            ->once()->with(self::DEALER_ID, $overlayParams)
            ->andReturn($overlayParams);

        $this->userRepositoryMock->shouldReceive('get')
            ->once()->with(['dealer_id' => self::DEALER_ID])
            ->andReturn($userMock);

        $this->imageService->updateOverlaySettings($overlayParams);

        Queue::assertNotPushed(GenerateOverlayImageJob::class);
        Queue::assertPushed(GenerateOverlayImageJobByDealer::class, 1);
    }

    /**
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testUpdateOverlaySettingsWithoutChanges()
    {
        $overlayParams = [
            'dealer_id' => self::DEALER_ID,
            'overlay_logo' => 'logo.png'
        ];

        $this->inventoryRepositoryMock->shouldNotReceive('getAll');

        $this->inventoryRepositoryMock->shouldNotReceive('massUpdate');

        $this->userRepositoryMock->shouldReceive('updateOverlaySettings')
            ->once()->with(self::DEALER_ID, $overlayParams)
            ->andReturn([]);

        $this->userRepositoryMock->shouldReceive('get')
            ->once()->with(['dealer_id' => self::DEALER_ID])
            ->andReturn($this->getEloquentMock(User::class));

        $this->imageService->updateOverlaySettings($overlayParams);

        Queue::assertNotPushed(GenerateOverlayImageJob::class);
        Queue::assertNotPushed(ReIndexInventoriesByDealersJob::class);
    }
}
