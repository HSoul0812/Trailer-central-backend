<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Tests\Unit\App\Services\Integrations\TrailerCentral\Console\AbstractSyncService;

use App\Models\SyncProcess;
use App\Services\Integrations\TrailerCentral\Console\AbstractSyncService;
use Exception;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Unit\WithFaker;

/**
 * @covers \App\Services\Integrations\TrailerCentral\Console\AbstractSyncService::applyToTransaction
 */
class ApplyToTransactionTest extends SyncServiceTestCase
{
    use WithFaker;

    /**
     * Test that SUT will not finish the process when there was an exception.
     */
    public function testWillNotFinishTheProcess(): void
    {
        /** @var AbstractSyncService|MockObject $serviceMock */
        /** @var \PHPUnit\Framework\MockObject\MockObject|SyncProcess $process */
        $processName = $this->faker->word();
        $processId = $this->faker->randomNumber();
        $numberOfRecordsImported = 0;
        $isNotTheFirstImport = false;
        $chunkLimit = 4;

        $exception = new Exception('Some error has happened');

        $builder = $this->mockClassWithoutArguments(Builder::class);

        $process = $this->mockEloquent(SyncProcess::class, ['id' => $processId, 'name' => $processName]);

        $dependencies = $this->mockDependencies();

        $dependencies['sourceRepository']->expects($this->once())
            ->method('queryAllSince')
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('chunk')
            ->with($chunkLimit, fn ($records) => true)
            ->willThrowException($exception);

        $dependencies['processRepository']->expects($this->never())
            ->method('finishById');

        $serviceMock = $this->getMockForAbstractClass(AbstractSyncService::class, $dependencies);

        $this->setProperty($serviceMock, 'currentProcess', $process);
        $this->setProperty($serviceMock, 'numberOfRecordsImported', $numberOfRecordsImported);
        $this->setProperty($serviceMock, 'isNotTheFirstImport', $isNotTheFirstImport);

        $serviceMock->expects($this->once())->method('getChunkLimit')->willReturn($chunkLimit);

        $this->expectException($exception::class);
        $this->expectExceptionMessage($exception->getMessage());

        $this->invokeMethod($serviceMock, 'applyToTransaction');
    }

    public function testWillFinishTheProcessAsExpected(): void
    {
        /** @var AbstractSyncService|MockObject $serviceMock */
        /** @var \PHPUnit\Framework\MockObject\MockObject|SyncProcess $process */
        $processName = $this->faker->word();
        $processId = $this->faker->randomNumber();
        $numberOfRecordsImported = 0;
        $isNotTheFirstImport = false;
        $chunkLimit = 4;

        $builder = $this->mockClassWithoutArguments(Builder::class);

        $process = $this->mockEloquent(SyncProcess::class, ['id' => $processId, 'name' => $processName]);

        $dependencies = $this->mockDependencies();

        $dependencies['sourceRepository']->expects($this->once())
            ->method('queryAllSince')
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('chunk')
            ->with($chunkLimit, fn ($records) => true);

        $dependencies['processRepository']->expects($this->once())
            ->method('finishById')
            ->with($process->id, ['numberOfRecordsImported' => $numberOfRecordsImported]);

        $serviceMock = $this->getMockForAbstractClass(AbstractSyncService::class, $dependencies);

        $this->setProperty($serviceMock, 'currentProcess', $process);
        $this->setProperty($serviceMock, 'numberOfRecordsImported', $numberOfRecordsImported);
        $this->setProperty($serviceMock, 'isNotTheFirstImport', $isNotTheFirstImport);

        $serviceMock->expects($this->once())->method('getChunkLimit')->willReturn($chunkLimit);

        $this->invokeMethod($serviceMock, 'applyToTransaction');
    }
}
