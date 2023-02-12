<?php

namespace Tests\Unit\Jobs\Inventory;

use Tests\TestCase;
use App\Services\Inventory\InventoryServiceInterface;
use App\Jobs\Inventory\GenerateOverlayImageJob;
use Mockery;
use Mockery\LegacyMockInterface;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use Exception;

class GenerateOverlayImageJobTest extends TestCase {

    const INVENTORY_ID = PHP_INT_MAX;

    /**
     * @var LegacyMockInterface|InventoryServiceInterface
     */
    private $inventoryServiceMock;

    /**
     * @var LoggerInterface|LegacyMockInterface
     */
    protected $logMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryServiceMock = Mockery::mock(InventoryServiceInterface::class);
        $this->app->instance(InventoryServiceInterface::class, $this->inventoryServiceMock);

        $this->instanceMock('logMock', LoggerInterface::class);
    }

    /**
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testHandle()
    {
        $this->inventoryServiceMock
            ->shouldReceive('generateOverlays')
            ->with(self::INVENTORY_ID)
            ->once();

        Log::shouldReceive('channel')
            ->once()
            ->andReturn($this->logMock);

        $this->logMock
            ->shouldReceive('error')
            ->never();

        $this->logMock
            ->shouldReceive('info')
            ->once();

        $job = $this->getMockBuilder(GenerateOverlayImageJob::class)
            ->setConstructorArgs([
                self::INVENTORY_ID
            ])
            ->onlyMethods(['release'])
            ->getMock();

        $job->expects($this->never())->method('release');

        $job->handle($this->inventoryServiceMock);
    }

    /**
     * ps: assuming there's a race condition when transaction commit is taking longer to process when adding new inventory
     * 
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testHandleMissingInventoryException()
    {
        $this->inventoryServiceMock
            ->shouldReceive('generateOverlays')
            ->with(self::INVENTORY_ID)
            ->once()
            ->andThrow(new Exception(GenerateOverlayImageJob::MISSING_INVENTORY_ERROR_MESSAGE));

        Log::shouldReceive('channel')
            ->once()
            ->andReturn($this->logMock);

        $this->logMock
            ->shouldReceive('error')
            ->times(2);

        $this->logMock
            ->shouldReceive('info')
            ->never();

        $job = $this->getMockBuilder(GenerateOverlayImageJob::class)
            ->setConstructorArgs([
                self::INVENTORY_ID
            ])
            ->onlyMethods(['release'])
            ->getMock();

        $job->expects($this->once())->method('release');

        $job->handle($this->inventoryServiceMock);
    }

    /**
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testHandleException()
    {
        $this->inventoryServiceMock
            ->shouldReceive('generateOverlays')
            ->with(self::INVENTORY_ID)
            ->once()
            ->andThrow(Exception::class);

        Log::shouldReceive('channel')
            ->once()
            ->andReturn($this->logMock);

        $this->logMock
            ->shouldReceive('error')
            ->times(2);

        $this->logMock
            ->shouldReceive('info')
            ->never();

        $job = $this->getMockBuilder(GenerateOverlayImageJob::class)
            ->setConstructorArgs([
                self::INVENTORY_ID
            ])
            ->onlyMethods(['release'])
            ->getMock();

        $job->expects($this->never())->method('release');

        $job->handle($this->inventoryServiceMock);
    }
}