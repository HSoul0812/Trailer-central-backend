<?php

namespace Tests\Unit\Jobs\Inventory;

use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Services\Inventory\InventoryServiceInterface;
use App\Jobs\Inventory\GenerateOverlayImageJob;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Inventory\Inventory;
use Mockery\LegacyMockInterface;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use Tests\TestCase;
use Exception;
use Mockery;

/**
 * @group DW
 * @group DW_INVENTORY
 * @group DW_ELASTICSEARCH
 *
 * @covers \App\Jobs\Inventory\GenerateOverlayImageJob
 */
class GenerateOverlayImageJobTest extends TestCase
{
    use WithFaker;

    /** @var int  */
    const INVENTORY_ID = PHP_INT_MAX;

    /** @var LegacyMockInterface|InventoryServiceInterface */
    private $inventoryServiceMock;

    /** @var LegacyMockInterface|InventoryRepositoryInterface */
    private $inventoryRepositoryMock;

    /** @var LoggerInterface|LegacyMockInterface */
    protected $logMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryServiceMock = Mockery::mock(InventoryServiceInterface::class);
        $this->app->instance(InventoryServiceInterface::class, $this->inventoryServiceMock);

        $this->inventoryRepositoryMock = Mockery::mock(InventoryRepositoryInterface::class);
        $this->app->instance(InventoryRepositoryInterface::class, $this->inventoryServiceMock);

        $this->logMock = Mockery::mock(LoggerInterface::class);
    }

    /**
     * Test that SUT will go through sad path by catching an exception, logging it and finally, given
     * the job was instantiated to do not index in ElasticSearch and invalidate cache, then it will not spawn
     * jobs to handle those processes
     *
     * @group Marketing
     * @group Marketing_Overlays
     * @covers ::handle
     */
    public function testHandleWillCatchExceptionAndLogWithoutIndexingAndInvalidatingCache()
    {
        $inventoryId = $this->faker->numberBetween(600, 50000);
        $shouldIndexAndInvalidateCache = false;

        $this->inventoryServiceMock
            ->allows('generateOverlays')
            ->with($inventoryId)
            ->andThrow(Exception::class);

        $this->logMock
            ->expects('error')
            ->twice();

        Log::shouldReceive('channel')
            ->once()
            ->andReturn($this->logMock);

        $job = new GenerateOverlayImageJob($inventoryId, $shouldIndexAndInvalidateCache);

        $job->handle($this->inventoryServiceMock, $this->inventoryRepositoryMock);
    }

    /**
     * Test that SUT will go through sad path by catching an exception, logging it and finally, given
     * the job was instantiated to do index in ElasticSearch and invalidate cache, then it will spawn
     * jobs to handle those processes
     *
     * @group Marketing
     * @group Marketing_Overlays
     * @covers ::handle
     */
    public function testHandleWillCatchExceptionAndLogIndexingAndInvalidatingCache()
    {
        $inventoryId = $this->faker->numberBetween(600, 50000);
        $dealerId = $this->faker->numberBetween(50000, 80000);
        $shouldIndexAndInvalidateCache = true;

        /** @var Inventory|LegacyMockInterface $inventoryMock */
        $inventoryMock = $this->getEloquentMock(Inventory::class);
        $inventoryMock->inventory_id = $inventoryId;
        $inventoryMock->dealer_id = $dealerId;

        $this->inventoryServiceMock
            ->allows('generateOverlays')
            ->with($inventoryId)
            ->andThrow(Exception::class);

        $this->inventoryServiceMock
            ->allows('tryToIndexAndInvalidateInventory')
            ->with($inventoryMock);

        $this->inventoryRepositoryMock
            ->allows('get')
            ->with(['id' => $inventoryId])
            ->andReturn($inventoryMock);

        $this->logMock
            ->expects('error')
            ->twice();

        $this->logMock
            ->expects('info')
            ->with('it will dispatch jobs for sync to index and invalidate cache',[
                'inventory_id' => $inventoryId, 'dealer_id' => $inventoryMock->dealer_id
            ])
            ->once();

        Log::shouldReceive('channel')
            ->once()
            ->andReturn($this->logMock);

        $job = new GenerateOverlayImageJob($inventoryId, $shouldIndexAndInvalidateCache);

        $job->handle($this->inventoryServiceMock, $this->inventoryRepositoryMock);
    }
}
