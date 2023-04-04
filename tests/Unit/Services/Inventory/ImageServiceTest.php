<?php

namespace Tests\Unit\Services\Inventory;

use App\Exceptions\File\MissingS3FileException;
use App\Jobs\Inventory\GenerateOverlayImageJobByDealer;
use App\Jobs\Inventory\ReIndexInventoriesByDealersJob;
use App\Models\Inventory\Inventory;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;
use App\Jobs\Inventory\GenerateOverlayImageJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use App\Models\User\User;
use App\Services\Inventory\ImageServiceInterface;
use App\Services\Inventory\ImageService;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Models\Inventory\Image;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use Mockery\LegacyMockInterface;

/**
 * @group DW
 * @group DW_ELASTICSEARCH
 * @group DW_INVENTORY
 *
 * @coversDefaultClass \App\Services\Inventory\ImageService
 */
class ImageServiceTest extends TestCase
{
    use WithFaker;

    /** @var int  */
    const ONCE = 1;

    /** @var int  */
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
     * Test that SUT will throw an specific exception when the S3 object doesn't exists
     *
     * @group Marketing
     * @group Marketing_Overlays
     * @covers ::saveOverlay
     */
    public function testSaveOverlayWillThrowExceptionWhenThereIsNotOverlayYet()
    {
        $newImageOverlay = 'image_with_overlay_1';

        /** @var Image|LegacyMockInterface $image1 */
        $image1 = $this->getEloquentMock(Image::class);
        $image1->image_id = 1;
        $image1->filename = 'image_1';

        $this->expectException(MissingS3FileException::class);
        $this->expectExceptionMessage(sprintf("S3 object '%s' is missing", $newImageOverlay));

        $this->imageService->saveOverlay($image1, $newImageOverlay);
    }

    /**
     * Test that SUT will save the image when the S3 object exists, then it will mark the image as overlay generated,
     * finally it will mark previous S3 object to be dropped from storage
     *
     * @group Marketing
     * @group Marketing_Overlays
     * @covers ::saveOverlay
     */
    public function testSaveOverlayWithEmptyNoverlay()
    {
        Storage::fake('s3');

        $oldImageOverlay = 'image_with_overlay_1_'.$this->faker->slug('2');
        $newImageOverlay = 'image_with_overlay_2_'.$this->faker->slug('2');

        Storage::disk('s3')->put($oldImageOverlay, '');
        Storage::disk('s3')->put($newImageOverlay, '');

        /** @var Image|LegacyMockInterface $image1 */
        $image1 = $this->getEloquentMock(Image::class);
        $image1->image_id = 1;
        $image1->filename = $oldImageOverlay;

        $this->imageRepositoryMock->expects('update')
            ->with([
                'filename' => $newImageOverlay,
                'filename_with_overlay' => $newImageOverlay,
                'hash' => 'test_hash',
                'id' => 1
            ]);

        $this->imageService->expects($this->once())
            ->method('getFileHash')
            ->willReturn('test_hash');

        $this->inventoryRepositoryMock->expects('markImageAsOverlayGenerated')
            ->with($image1->image_id);

        $this->imageRepositoryMock->expects('scheduleObjectToBeDroppedByURL')
            ->with($image1->filename);

        $this->imageService->saveOverlay($image1, $newImageOverlay);

        Storage::disk('s3')->assertExists([
            $oldImageOverlay,
            $newImageOverlay
        ]);
    }

    /**
     * Test that SUT will do nothing when the image doesn't have an overlay yet
     *
     * @group Marketing
     * @group Marketing_Overlays
     * @covers ::tryToRestoreOriginalImage
     */
    public function testResetOverlayWillDoNothingWhenNoOverlay()
    {
        /** @var Image|LegacyMockInterface $image1 */
        $image1 = $this->getEloquentMock(Image::class);
        $image1->image_id = 1;
        $image1->filename = 'image_1';
        $image1->filename_with_overlay = null;

        $this->imageRepositoryMock->allows('update')->never();

        $this->imageService->tryToRestoreOriginalImage($image1);
    }

    /**
     * Test that SUT will reset the overlay image by restoring the original image, also both S3 objects should remains
     *
     * @group Marketing
     * @group Marketing_Overlays
     * @covers ::tryToRestoreOriginalImage
     */
    public function testResetOverlayWithEmptyNoverlay()
    {
        Storage::fake('s3');

        $imageOverlay = 'image_with_overlay_1_'.$this->faker->slug('2');
        $originalImage = 'image_without_overlay_'.$this->faker->slug('2');

        Storage::disk('s3')->put($imageOverlay, '');
        Storage::disk('s3')->put($originalImage, '');

        /** @var Image|LegacyMockInterface $image1 */
        $image1 = $this->getEloquentMock(Image::class);
        $image1->image_id = 1;
        $image1->filename = $imageOverlay;
        $image1->filename_with_overlay = $imageOverlay;
        $image1->filename_without_overlay = $originalImage;

        $this->imageService->expects($this->once())
            ->method('getFileHash')
            ->willReturn('test_hash');

        $this->imageRepositoryMock->allows('update')->with([
            'id' => $image1->image_id,
            'hash' => 'test_hash',
            'filename' => $image1->filename_without_overlay
        ]);

        $this->imageService->tryToRestoreOriginalImage($image1);

        Storage::disk('s3')->assertExists([
            $imageOverlay,
            $originalImage
        ]);
    }

    /**
     * Test that SUT will update dealer overlay configuration but it will not massively update
     * inventory overlay configuration because `overlay_enabled` was not changed, finally it should
     * have dispatched a `GenerateOverlayImageJobByDealer` to process all inventory overlay changes
     *
     * @group Marketing
     * @group Marketing_Overlays
     * @covers ::updateOverlaySettings
     */
    public function testUpdateOverlaySettings()
    {
        $overlayParams = [
            'dealer_id' => self::DEALER_ID,
            'overlay_logo' => 'logo.png'
        ];

        /** @var User|LegacyMockInterface $userMock */
        $userMock = $this->getEloquentMock(User::class);
        $userMock->dealer_id = self::DEALER_ID;

        $this->inventoryRepositoryMock->allows('massUpdate')->never();

        $this->userRepositoryMock->expects('updateOverlaySettings')
            ->with(self::DEALER_ID, $overlayParams)
            ->andReturns($overlayParams);

        $this->userRepositoryMock->expects('get')
            ->with(['dealer_id' => self::DEALER_ID])
            ->andReturns($userMock);

        $this->imageService->updateOverlaySettings($overlayParams);

        Queue::assertNotPushed(GenerateOverlayImageJob::class);
        Queue::assertNotPushed(ReIndexInventoriesByDealersJob::class);
        Queue::assertPushed(GenerateOverlayImageJobByDealer::class, self::ONCE);
    }

    /**
     * Test that SUT will update dealer overlay configuration, also it will massively update
     * inventory overlay configuration because `overlay_enabled` was changed, finally it should
     * have dispatched a `GenerateOverlayImageJobByDealer` to process all inventory overlay changes
     *
     * @group Marketing
     * @group Marketing_Overlays
     * @covers ::updateOverlaySettings
     */
    public function testUpdateOverlaySettingsWithOverlayEnabledChanged()
    {
        $overlayParams = [
            'dealer_id' => self::DEALER_ID,
            'overlay_logo' => 'logo.png',
            'overlay_enabled' => Inventory::OVERLAY_ENABLED_ALL
        ];

        $performedChanges = [
            'overlay_logo' => 'logo.png',
            'overlay_enabled' => Inventory::OVERLAY_ENABLED_ALL
        ];

        /** @var User|LegacyMockInterface $userMock */
        $userMock = $this->getEloquentMock(User::class);
        $userMock->dealer_id = self::DEALER_ID;

        $this->inventoryRepositoryMock->expects('massUpdate')
            ->with([
                'dealer_id' => $overlayParams['dealer_id'],
                'overlay_enabled' => $overlayParams['overlay_enabled']
            ]);

        $this->userRepositoryMock->expects('updateOverlaySettings')
            ->with(self::DEALER_ID, $overlayParams)
            ->andReturns($performedChanges);

        $this->userRepositoryMock->expects('get')
            ->with(['dealer_id' => self::DEALER_ID])
            ->andReturns($userMock);

        $this->imageService->updateOverlaySettings($overlayParams);

        Queue::assertNotPushed(GenerateOverlayImageJob::class);
        Queue::assertNotPushed(ReIndexInventoriesByDealersJob::class);
        Queue::assertPushed(GenerateOverlayImageJobByDealer::class, self::ONCE);
    }

    /**
     * Test that SUT will try to update dealer overlay configuration, then, it should not perform any other action
     * due there was not changes
     *
     * @group Marketing
     * @group Marketing_Overlays
     * @covers ::updateOverlaySettings
     */
    public function testUpdateOverlaySettingsWithoutChanges()
    {
        $performedChanges = []; // there was not changes

        $overlayParams = [
            'dealer_id' => self::DEALER_ID,
            'overlay_logo' => 'logo.png'
        ];

        $this->inventoryRepositoryMock->allows('getAll')->never();

        $this->inventoryRepositoryMock->allows('massUpdate')->never();

        $this->userRepositoryMock->expects('updateOverlaySettings')
            ->with(self::DEALER_ID, $overlayParams)
            ->andReturns($performedChanges);

        $this->userRepositoryMock->expects('get')
            ->with(['dealer_id' => self::DEALER_ID])
            ->andReturns($this->getEloquentMock(User::class));

        $this->imageService->updateOverlaySettings($overlayParams);

        Queue::assertNotPushed(GenerateOverlayImageJob::class);
        Queue::assertNotPushed(ReIndexInventoriesByDealersJob::class);
    }

    /**
     * Test that SUT will try to update dealer overlay configuration, then, it should perform only inventory `massUpdate`
     * but it will not dispatch jobs to generate overlays
     *
     * @group Marketing
     * @group Marketing_Overlays
     * @covers ::updateOverlaySettings
     */
    public function testUpdateOverlaySettingsOnlyChangedWasOverlayEnabled()
    {
        $performedChanges = ['overlay_enabled' => Inventory::OVERLAY_ENABLED_ALL];
        $overlayParams = array_merge(['dealer_id' => self::DEALER_ID], $performedChanges);
        /** @var User $dealer */
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = self::DEALER_ID;

        $this->inventoryRepositoryMock->expects('massUpdate')
            ->with([
                'dealer_id' => $overlayParams['dealer_id'],
                'overlay_enabled' => $overlayParams['overlay_enabled']
            ]);

        $this->userRepositoryMock->expects('updateOverlaySettings')
            ->with(self::DEALER_ID, $overlayParams)
            ->andReturns($performedChanges);

        $this->userRepositoryMock->expects('get')
            ->with(['dealer_id' => self::DEALER_ID])
            ->andReturns($dealer);

        $this->imageService->updateOverlaySettings($overlayParams);

        Queue::assertNotPushed(GenerateOverlayImageJob::class);
        Queue::assertNotPushed(ReIndexInventoriesByDealersJob::class);
        Queue::assertPushed(GenerateOverlayImageJobByDealer::class, self::ONCE);
    }
}
