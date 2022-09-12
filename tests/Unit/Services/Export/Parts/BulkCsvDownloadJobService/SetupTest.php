<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Export\Parts\BulkCsvDownloadJobService;

use App\Exceptions\Common\BusyJobException;
use App\Models\Bulk\Parts\BulkDownload;
use App\Models\Bulk\Parts\BulkDownloadPayload;
use App\Services\Export\Parts\BulkCsvDownloadJobService;
use App\Services\Import\Parts\CsvImportService;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use Exception;

/**
 * @covers \App\Services\Export\Parts\BulkCsvDownloadJobService::setup
 * @group MonitoredJobs
 */
class SetupTest extends TestCase
{
    use WithFaker;

    /**
     * Test that when there is another monitored job working (same dealer), it will throw a `BusyJobException`
     *
     * @group DMS
     * @group DMS_BULK_DOWNLOAD
     *
     * @throws Exception
     */
    public function testWillThrowAnException(): void
    {
        // Given I have the three dependencies for "BulkCsvDownloadJobService" creation
        $dependencies = new BulkCsvDownloadJobServiceDependencies();
        // And I'm a dealer with a specific id
        $dealerId = $this->faker->unique()->numberBetween(100, 50000);
        // And I have a specific token from a monitored job
        $token = Uuid::uuid4()->toString();
        // And I have a specific payload
        $payload = BulkDownloadPayload::from(['export_file' => 'parts-' . date('Ymd') . '-' . $token . '.csv']);

        // Then I expect that repository isBusyByDealer method is called with certain arguments and it will return true
        $dependencies->bulkDownloadRepository
            ->shouldReceive('isBusyByDealer')
            ->once()
            ->with($dealerId)
            ->andReturn(true);

        // Also I have a "BulkCsvDownloadJobService" properly created
        $service = new BulkCsvDownloadJobService(
            $dependencies->bulkDownloadRepository,
            $dependencies->partsRepository,
            $dependencies->loggerService,
            $dependencies->jobsRepository
        );

        // Then I expect to see an specific exception to be thrown
        $this->expectException(BusyJobException::class);
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage("This job can't be set up due there is currently other job working");

        // When I call the run method
        $service->setup($dealerId, $payload, $token);
    }

    /**
     * @group DMS
     * @group DMS_BULK_DOWNLOAD
     *
     * @throws Exception
     */
    public function testWillCreateMonitoredJob(): void
    {
        /** @var MockInterface|LegacyMockInterface|CsvImportService $service */

        // Given I have the four required dependencies for "BulkCsvDownloadJobServiceDependencies" creation
        $dependencies = new BulkCsvDownloadJobServiceDependencies();
        // And I'm a dealer with a specific id
        $dealerId = $this->faker->unique()->numberBetween(100, 50000);
        // And I have a specific token from a monitored job
        $token = Uuid::uuid4()->toString();
        // And I have a specific payload
        $payload = BulkDownloadPayload::from(['export_file' => 'parts-' . date('Ymd') . '-' . $token . '.csv']);

        // Then I expect that a known "BulkDownload" model is going to be returned
        $expectedBulkDownload = new BulkDownload([
            'dealer_id' => $dealerId,
            'token' => $token,
            'payload' => $payload->asArray(),
            'queue' => BulkDownload::QUEUE_NAME,
            'concurrency_level' => BulkDownload::LEVEL_DEFAULT,
            'name' => BulkDownload::QUEUE_JOB_NAME
        ]);

        // And I expect that repository isBusyByDealer method is called with certain arguments and it will return false
        $dependencies->bulkDownloadRepository
            ->shouldReceive('isBusyByDealer')
            ->once()
            ->with($dealerId)
            ->andReturn(false);
        // And I expect that repository create method is called with certain arguments
        $dependencies->bulkDownloadRepository
            ->shouldReceive('create')
            ->once()
            ->with([
                'dealer_id' => $dealerId,
                'token' => $token,
                'payload' => is_array($payload) ? $payload : $payload->asArray(),
                'queue' => BulkDownload::QUEUE_NAME,
                'concurrency_level' => BulkDownload::LEVEL_DEFAULT,
                'name' => BulkDownload::QUEUE_JOB_NAME
            ])
            ->andReturn($expectedBulkDownload);

        // Also I have a "BulkCsvDownloadJobService" properly created
        $service = new BulkCsvDownloadJobService(
            $dependencies->bulkDownloadRepository,
            $dependencies->partsRepository,
            $dependencies->loggerService,
            $dependencies->jobsRepository
        );

        // When I call the run method
        $bulkDownload = $service->setup($dealerId, $payload, $token);

        // Then I expect to receive a new "BulkDownload" model
        self::assertSame($expectedBulkDownload, $bulkDownload);
    }
}
