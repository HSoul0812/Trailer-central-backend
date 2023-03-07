<?php

namespace Tests\Integration\Jobs\Inventory;

use Tests\TestCase;
use App\Services\Inventory\InventoryServiceInterface;
use App\Jobs\Inventory\GenerateOverlayImageJob;
use Mockery\LegacyMockInterface;
use Illuminate\Support\Facades\Log;

class GenerateOverlayImageJobTest extends TestCase {

    const NON_EXISTING_INVENTORY_ID = PHP_INT_MAX;

    /**
     * @var LoggerInterface|LegacyMockInterface
     */
    protected $logMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->instanceMock('logMock', LoggerInterface::class);
    }

    /**
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testMissingInventoryExceptionMessage()
    {
        Log::shouldReceive('channel')
            ->once()
            ->andReturn($this->logMock);

        $this->logMock->shouldReceive('error')
            ->with(GenerateOverlayImageJob::MISSING_INVENTORY_ERROR_MESSAGE)
            ->once();

        $this->logMock->shouldReceive('error')
            ->once();

        $service = app()->make(InventoryServiceInterface::class);

        $job = new GenerateOverlayImageJob(self::NON_EXISTING_INVENTORY_ID);

        $job->handle($service);
    }
}