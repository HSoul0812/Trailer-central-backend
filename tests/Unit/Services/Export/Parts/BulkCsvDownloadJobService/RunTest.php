<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Export\Parts\BulkCsvDownloadJobService;

use App\Models\Bulk\Parts\BulkDownload;
use App\Services\Export\Parts\BulkCsvDownloadJobService;
use App\Services\Export\Parts\FilesystemCsvExporter;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Exception;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

/**
 * @covers \App\Services\Export\Parts\BulkCsvDownloadJobService::run
 * @group MonitoredJobs
 */
class RunTest extends TestCase
{
    use WithFaker;

    /**
     * @group DMS
     * @group DMS_BULK_DOWNLOAD
     *
     * @throws Exception
     */
    public function testWillThrowAnException(): void
    {
        /** @var  MockInterface|LegacyMockInterface|BulkCsvDownloadJobService $service */

        // Given I have the four dependencies for "BulkCsvDownloadJobService" creation
        $dependencies = new BulkCsvDownloadJobServiceDependencies();
        // And I have a "BulkDownload" model
        $token = Uuid::uuid4()->toString();
        $job = new BulkDownload([
            'dealer_id' => $this->faker->unique()->numberBetween(100, 50000),
            'token' => $token,
            'payload' => ['export_file' => 'parts-' . date('Ymd') . '-' . $token . '.csv'],
            'queue' => BulkDownload::QUEUE_NAME,
            'concurrency_level' => BulkDownload::LEVEL_DEFAULT,
            'name' => BulkDownload::QUEUE_JOB_NAME
        ]);
        // And I know what exception will be thrown
        $exception = new Exception('This a dummy exception', 500);

        // Then I expect that a log entry is stored
        $dependencies->loggerService->shouldReceive('info')->once();
        // And I expect that parts repository queryAllByDealerId method is called with certain arguments
        $dependencies->partsRepository
            ->shouldReceive('queryAllByDealerId')
            ->once()
            ->with($job->dealer_id);
        // And I expect that bulk repository updateProgress method is called with certain arguments
        $dependencies->bulkDownloadRepository
            ->shouldReceive('updateProgress')
            ->once()
            ->with($job->token, 0)
            ->andReturnTrue();
        // And I expect that an error log entry is stored
        $dependencies->loggerService->shouldReceive('error')->once();
        // And I expect that bulk repository setFailed method is called with certain arguments
        $dependencies->bulkDownloadRepository
            ->shouldReceive('setFailed')
            ->once()
            ->with($job->token, ['message' => "Got exception: {$exception->getMessage()}"]);

        // Also I have a "BulkCsvDownloadJobService" properly created
        $service = Mockery::mock(BulkCsvDownloadJobService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        // And I have a "FilesystemCsvExporter" properly created
        $fileStorage = Mockery::mock(
            FilesystemCsvExporter::class,
            [Storage::fake('partsCsvExports'), $job->payload->export_file]
        )
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $fileStorage->shouldReceive('export')->once()->andThrow($exception);
        // Then I expect "getExporter" is called once and return the "FilesystemCsvExporter"
        $service->shouldReceive('getExporter')->once()->andReturn($fileStorage);
        // And I expect to see an specific exception to be thrown
        $this->expectException(get_class($exception));
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage($exception->getMessage());

        // When I call the run method
        $service->run($job);
    }

    /**
     * @group DMS
     * @group DMS_BULK_DOWNLOAD
     *
     * @throws Exception
     */
    public function testWillExport(): void
    {
        /** @var  MockInterface|LegacyMockInterface|BulkCsvDownloadJobService $service */

        // Given I have the four dependencies for "BulkCsvDownloadJobService" creation
        $dependencies = new BulkCsvDownloadJobServiceDependencies();
        // And I have a "BulkDownload" model
        $token = Uuid::uuid4()->toString();
        $job = new BulkDownload([
            'dealer_id' => $this->faker->unique()->numberBetween(100, 50000),
            'token' => $token,
            'payload' => ['export_file' => 'parts-' . date('Ymd') . '-' . $token . '.csv'],
            'queue' => BulkDownload::QUEUE_NAME,
            'concurrency_level' => BulkDownload::LEVEL_DEFAULT,
            'name' => BulkDownload::QUEUE_JOB_NAME
        ]);

        // Then I expect that a log entry is stored
        $dependencies->loggerService->shouldReceive('info')->once();
        // And I expect that parts repository queryAllByDealerId method is called with certain arguments
        $dependencies->partsRepository
            ->shouldReceive('queryAllByDealerId')
            ->once()
            ->with($job->dealer_id);
        // And I expect that bulk repository updateProgress method is called with certain arguments
        $dependencies->bulkDownloadRepository
            ->shouldReceive('updateProgress')
            ->once()
            ->with($job->token, 0)
            ->andReturnTrue();
        // And I expect that bulk repository setCompleted method is called with certain arguments
        $dependencies->bulkDownloadRepository
            ->shouldReceive('setCompleted')
            ->once()
            ->with($job->token);

        // Also I have a "BulkCsvDownloadJobService" properly created
        $service = Mockery::mock(BulkCsvDownloadJobService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        // And I have a "FilesystemCsvExporter" properly created
        $fileStorage = Mockery::mock(
            FilesystemCsvExporter::class,
            [Storage::fake('partsCsvExports'), $job->payload->export_file]
        )
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $fileStorage->shouldReceive('export')->once();
        // Then I expect "getExporter" is called once and return the "FilesystemCsvExporter"
        $service->shouldReceive('getExporter')->once()->andReturn($fileStorage);

        // When I call the run method
        $service->run($job);
    }
}
