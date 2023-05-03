<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Tests\Unit\App\Services\Integrations\TrailerCentral\Console\AbstractSyncService;

use App\Exceptions\CannotBeUsedBeyondConsole;
use App\Models\SyncProcess;
use App\Services\Integrations\TrailerCentral\Console\AbstractSyncService;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Unit\WithFaker;

/**
 * @covers \App\Services\Integrations\TrailerCentral\Console\AbstractSyncService::sync
 */
class SyncTest extends SyncServiceTestCase
{
    use WithFaker;

    /**
     * Test that SUT will throw a specific exception when the command has been performed out of console.
     */
    public function testWillThrowAnExceptionWhenNotConsole(): void
    {
        /** @var AbstractSyncService|MockObject $serviceMock */
        $dependencies = $this->mockDependencies();

        $dependencies['app']->expects($this->once())
            ->method('runningInConsole')
            ->willReturn(false);

        $serviceMock = $this->getMockForAbstractClass(AbstractSyncService::class, $dependencies);

        $this->expectException(CannotBeUsedBeyondConsole::class);
        $this->expectExceptionMessage(CannotBeUsedBeyondConsole::MESSAGE);

        $serviceMock->sync();
    }

    /**
     * Test that SUT will throw an exception when an unknown exception then will finish the process and write an error log.
     */
    public function testWillThrowAnExceptionWhenUnknownReason(): void
    {
        /** @var AbstractSyncService|MockObject $serviceMock */
        /** @var \PHPUnit\Framework\MockObject\MockObject|SyncProcess $process */
        $memoryLimit = '450M';
        $processName = $this->faker->word();
        $processId = $this->faker->randomNumber();
        $exception = new Exception('Some error has happened');

        $process = $this->mockEloquent(SyncProcess::class, ['id' => $processId, 'name' => $processName]);

        $dependencies = $this->mockDependencies();

        $dependencies['app']->expects($this->once())
            ->method('runningInConsole')
            ->willReturn(true);

        $dependencies['processRepository']->expects($this->once())
            ->method('create')
            ->with(['name' => $processName])
            ->willReturn($process);

        $dependencies['processRepository']->expects($this->once())
            ->method('lastFinishedByProcessName')
            ->with($processName)
            ->willThrowException($exception);

        $dependencies['processRepository']->expects($this->once())
            ->method('failById')
            ->with($processId, ['errorMessage' => $exception->getMessage()])
            ->willReturn(true);

        $dependencies['logger']->expects($this->once())
            ->method('error')
            ->with(sprintf(
                '[SyncService::%s] process %d has failed due %s',
                $processName,
                $processId,
                $exception->getMessage()
            )
            );

        $this->expectException($exception::class);
        $this->expectExceptionMessage($exception->getMessage());

        $serviceMock = $this->getMockForAbstractClass(AbstractSyncService::class, $dependencies);

        $serviceMock->expects($this->once())->method('getMemoryLimit')->willReturn($memoryLimit);

        $serviceMock->expects($this->exactly(3))->method('getProcessName')->willReturn($processName);

        $serviceMock->sync();
    }

    public function testItWorksAsExpected(): void
    {
        /** @var AbstractSyncService|MockObject $serviceMock */
        /** @var \PHPUnit\Framework\MockObject\MockObject|SyncProcess $process */
        $memoryLimit = '450M';
        $processName = $this->faker->word();
        $processId = $this->faker->randomNumber();

        $process = $this->mockEloquent(SyncProcess::class, ['id' => $processId, 'name' => $processName]);

        $dependencies = $this->mockDependencies();

        $dependencies['app']->expects($this->once())
            ->method('runningInConsole')
            ->willReturn(true);

        $dependencies['processRepository']->expects($this->once())
            ->method('create')
            ->with(['name' => $processName])
            ->willReturn($process);

        $dependencies['processRepository']->expects($this->once())
            ->method('lastFinishedByProcessName')
            ->with($processName)
            ->willReturn(null);

        $dependencies['processRepository']->expects($this->once())
            ->method('isNotTheFirstImport')
            ->with($processName)
            ->willReturn(true);

        $serviceMock = $this->getMockForAbstractClass(AbstractSyncService::class, $dependencies);

        $dependencies['connection']->expects($this->once())->method('transaction');

        $serviceMock->expects($this->once())
            ->method('getMemoryLimit')
            ->willReturn($memoryLimit);

        $serviceMock->expects($this->exactly(3))
            ->method('getProcessName')
            ->willReturn($processName);

        $serviceMock->sync();
    }
}
