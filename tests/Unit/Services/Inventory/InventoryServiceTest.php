<?php

namespace Tests\Unit\Services\Inventory;

use App\Jobs\Files\DeleteS3FilesJob;
use App\Models\Inventory\File;
use App\Models\Inventory\Image;
use App\Repositories\Inventory\FileRepositoryInterface;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Repository;
use App\Services\Inventory\InventoryService;
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

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryRepositoryMock = Mockery::mock(InventoryRepositoryInterface::class);
        $this->app->instance(InventoryRepositoryInterface::class, $this->inventoryRepositoryMock);

        $this->imageRepositoryMock = Mockery::mock(ImageRepositoryInterface::class);
        $this->app->instance(ImageRepositoryInterface::class, $this->imageRepositoryMock);

        $this->fileRepositoryMock = Mockery::mock(FileRepositoryInterface::class);
        $this->app->instance(FileRepositoryInterface::class, $this->fileRepositoryMock);
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
                $this->fileRepositoryMock
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
            $this->fileRepositoryMock
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

    public function deleteParamsProvider()
    {
        return [[
            [Repository::RELATION_WITH_COUNT => 'inventoryImages'],
            [Repository::RELATION_WITH_COUNT => 'inventoryFiles']
        ]];
    }

    public function deleteDuplicatesParamsProvider()
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
}
