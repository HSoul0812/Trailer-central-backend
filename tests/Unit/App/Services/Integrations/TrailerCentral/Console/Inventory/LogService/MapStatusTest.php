<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Integrations\TrailerCentral\Console\Inventory\LogService;

use App\Models\Inventory\InventoryLog;
use App\Services\Integrations\TrailerCentral\Console\AbstractSyncService;
use App\Services\Integrations\TrailerCentral\Console\Inventory\LogService;
use PDO;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \App\Services\Integrations\TrailerCentral\Console\Inventory\LogService::mapStatus
 */
class MapStatusTest extends LogServiceTestCase
{
    /**
     * Test that SUT will behave as expected.
     *
     * @dataProvider statusesProvider
     *
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testWillMapAsExpected(?int $statusToMap, string $expectedStatus): void
    {
        /** @var AbstractSyncService|MockObject $serviceMock */
        $statusToMap = null;
        $expectedStatus = InventoryLog::STATUS_AVAILABLE;

        $dependencies = $this->mockDependencies();
        $pdo = $this->mockClassWithoutArguments(PDO::class);

        $dependencies['repository']->expects($this->never())
            ->method('lastByRecordId');

        $dependencies['connection']->expects($this->once())
            ->method('getPdo')
            ->willReturn($pdo);

        $serviceMock = $this->getMockBuilder(LogService::class)
            ->setConstructorArgs($dependencies)
            ->getMock();

        $mappedStatus = $this->invokeMethod($serviceMock, 'mapStatus', [$statusToMap]);

        $this->assertSame($expectedStatus, $mappedStatus);
    }

    /**
     * Examples of all available status which can be mapped.
     *
     * @return array<string, array<?int, string>>
     *
     * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
     */
    public function statusesProvider(): array
    {
        return [             // ?int $statusToMap, string $expectedStatus
            'null' => [null, InventoryLog::STATUS_AVAILABLE],
            'out of range' => [10, InventoryLog::STATUS_AVAILABLE],
            'when 2' => [2, InventoryLog::STATUS_SOLD],
            'when 3' => [3, InventoryLog::STATUS_SOLD],
            'when 4' => [4, InventoryLog::STATUS_SOLD],
            'when 5' => [5, InventoryLog::STATUS_SOLD],
            'when 6' => [6, InventoryLog::STATUS_SOLD],
        ];
    }
}
