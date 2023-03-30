<?php

namespace Tests\Integration\Jobs\Inventory;

use Psr\Log\LoggerInterface;
use Tests\TestCase;
use Mockery\LegacyMockInterface;

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
}
