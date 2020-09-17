<?php

namespace Tests\Unit\Services\Inventory;

use App\Jobs\Files\DeleteFilesJob;
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
     * @dataProvider getAllByInventoryIdParamsProvider
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

        $this->imageRepositoryMock
            ->shouldReceive('delete')
            ->with([
                Repository::CONDITION_AND_WHERE_IN => [
                    'image_id' => [$imageModel2->image_id]
                ]
            ])
            ->once();

        $this->expectsJobs(DeleteFilesJob::class);

        $this->fileRepositoryMock
            ->shouldReceive('getAllByInventoryId')
            ->once()
            ->with($inventoryId, $fileParams)
            ->andReturn($fileModels);

        $this->fileRepositoryMock
            ->shouldReceive('delete')
            ->with([
                Repository::CONDITION_AND_WHERE_IN => [
                    'id' => [$fileModel1->id]
                ]
            ])
            ->once();

        $this->inventoryRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with(['id' => $inventoryId])
            ->andReturn(true);

        $this->expectsJobs(DeleteFilesJob::class);

        Log::shouldReceive('info')
            ->with('Item has been successfully deleted', ['inventoryId' => $inventoryId]);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->delete($inventoryId);

        $this->assertTrue($result);
    }

    /**
     * @covers ::delete
     * @dataProvider getAllByInventoryIdParamsProvider
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

        $this->doesntExpectJobs(DeleteFilesJob::class);

        Log::shouldReceive('info')
            ->with('Item has been successfully deleted', ['inventoryId' => $inventoryId]);

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->delete($inventoryId);

        $this->assertTrue($result);
    }

    /**
     * @covers ::delete
     * @dataProvider getAllByInventoryIdParamsProvider
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

        $this->doesntExpectJobs(DeleteFilesJob::class);

        Log::shouldReceive('error')
            ->with('Item delete error.', $exception->getTrace());

        /** @var InventoryService $service */
        $service = $this->app->make(InventoryService::class);

        $result = $service->delete($inventoryId);

        $this->assertFalse($result);
    }

    public function getAllByInventoryIdParamsProvider()
    {
        return [[
            [Repository::RELATION_WITH_COUNT => 'inventoryImages'],
            [Repository::RELATION_WITH_COUNT => 'inventoryFiles']
        ]];
    }
}
