<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Tests\Unit\App\Services\Integrations\TrailerCentral\Console\AbstractSyncService;

use App\Models\SyncProcess;
use App\Services\Integrations\TrailerCentral\Console\AbstractSyncService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Tests\Unit\WithFaker;

/**
 * @covers \App\Services\Integrations\TrailerCentral\Console\AbstractSyncService::applyToChuck
 */
class ApplyToChunkTest extends SyncServiceTestCase
{
    use WithFaker;

    public function testWillImportTheChunkAsExpected(): void
    {
        /** @var AbstractSyncService|MockObject $serviceMock */
        /** @var \PHPUnit\Framework\MockObject\MockObject|SyncProcess $process */
        $processName = $this->faker->word();
        $processId = $this->faker->randomNumber();
        $numberOfRecordsImported = 0;
        $isNotTheFirstImport = false;

        $collection = Collection::make([
            new stdClass(),
            new stdClass(),
            new stdClass(),
            new stdClass(),
        ]);

        $process = $this->mockEloquent(SyncProcess::class, ['id' => $processId, 'name' => $processName]);

        $dependencies = $this->mockDependencies();

        $dependencies['targetRepository']->expects($this->exactly($collection->count()))
            ->method('mapToInsertString')
            ->with(new stdClass(), $isNotTheFirstImport)
            ->willReturn('INSERT INTO...');

        $dependencies['targetRepository']->expects($this->once())
            ->method('execute')
            ->with('INSERT INTO...INSERT INTO...INSERT INTO...INSERT INTO...');

        $dependencies['logger']->expects($this->once())
            ->method('info')
            ->with(sprintf(
                '[SyncService::%s] %d records imported on process %d',
                $processName,
                $collection->count(),
                $processId
            )
            );

        $serviceMock = $this->getMockForAbstractClass(AbstractSyncService::class, $dependencies);

        $this->setProperty($serviceMock, 'currentProcess', $process);
        $this->setProperty($serviceMock, 'numberOfRecordsImported', $numberOfRecordsImported);
        $this->setProperty($serviceMock, 'isNotTheFirstImport', $isNotTheFirstImport);

        $serviceMock->expects($this->once())->method('getProcessName')->willReturn($processName);

        $this->invokeMethod($serviceMock, 'applyToChuck', [$collection]);
    }
}
