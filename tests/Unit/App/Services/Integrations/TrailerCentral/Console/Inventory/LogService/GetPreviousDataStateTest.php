<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Unit\App\Services\Integrations\TrailerCentral\Console\Inventory\LogService;

use App\Models\Inventory\InventoryLog;
use App\Services\Integrations\TrailerCentral\Console\AbstractSyncService;
use App\Services\Integrations\TrailerCentral\Console\Inventory\LogService;
use PDO;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Unit\WithFaker;

/**
 * @covers \App\Services\Integrations\TrailerCentral\Console\Inventory\LogService::getPreviousDataState
 */
class GetPreviousDataStateTest extends LogServiceTestCase
{
    use WithFaker;

    public function testWillReturnNullWhenItIsTheFirstImport(): void
    {
        /** @var AbstractSyncService|MockObject $serviceMock */
        $isNotTheFirstImport = false;
        $recordId = $this->faker->randomNumber();

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

        $previousDataState = $this->invokeMethod(
            $serviceMock,
            'getPreviousDataState',
            [$isNotTheFirstImport, $recordId]
        );

        $this->assertNull($previousDataState);
    }

    public function testWillReturnNullThereIsNotPreviousState(): void
    {
        /** @var AbstractSyncService|MockObject $serviceMock */
        $isNotTheFirstImport = true;
        $recordId = $this->faker->randomNumber();

        $dependencies = $this->mockDependencies();
        $pdo = $this->mockClassWithoutArguments(PDO::class);

        $dependencies['repository']->expects($this->once())
            ->method('lastByRecordId')
            ->willReturn(null);

        $dependencies['connection']->expects($this->once())
            ->method('getPdo')
            ->willReturn($pdo);

        $serviceMock = $this->getMockBuilder(LogService::class)
            ->setConstructorArgs($dependencies)
            ->getMock();

        $previousDataState = $this->invokeMethod(
            $serviceMock,
            'getPreviousDataState',
            [$isNotTheFirstImport, $recordId]
        );

        $this->assertNull($previousDataState);
    }

    public function testWillReturnInventoryLogModel(): void
    {
        /** @var AbstractSyncService|MockObject $serviceMock */
        $isNotTheFirstImport = true;
        $recordId = $this->faker->randomNumber();
        $inventoryLog = $this->mockEloquent(InventoryLog::class);

        $dependencies = $this->mockDependencies();
        $pdo = $this->mockClassWithoutArguments(PDO::class);

        $dependencies['repository']->expects($this->once())
            ->method('lastByRecordId')
            ->willReturn($inventoryLog);

        $dependencies['connection']->expects($this->once())
            ->method('getPdo')
            ->willReturn($pdo);

        $serviceMock = $this->getMockBuilder(LogService::class)
            ->setConstructorArgs($dependencies)
            ->getMock();

        $previousDataState = $this->invokeMethod(
            $serviceMock,
            'getPreviousDataState',
            [$isNotTheFirstImport, $recordId]
        );

        $this->assertInstanceOf(InventoryLog::class, $previousDataState);
    }
}
