<?php

declare(strict_types=1);

namespace Tests\Integration\Jobs\Bulk\Inventory;

use App\Jobs\Bulk\Inventory\ProcessDownloadJob;
use App\Models\Bulk\Inventory\BulkDownload;
use App\Models\Bulk\Inventory\BulkDownloadPayload;
use App\Repositories\Bulk\Inventory\BulkDownloadRepositoryInterface;
use App\Services\Export\FilesystemPdfExporter;
use App\Services\Export\Inventory\Bulk\BulkDownloadJobService;
use App\Services\Export\Inventory\Bulk\BulkDownloadJobServiceInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\database\seeds\Common\MonitoredJobSeeder;
use Tests\Integration\AbstractMonitoredJobsTest;
use Throwable;

/**
 * @group DW
 * @group DW_INVENTORY
 *
 * @covers \App\Jobs\Bulk\Inventory\ProcessDownloadJob::handle
 * @group MonitoredJobs
 */
class ProcessDownloadJobTest extends AbstractMonitoredJobsTest
{
    /**
     * @dataProvider invalidConfigurationsProvider
     *
     * @group DW
     * @group DW_BULK
     * @group DW_BULK_INVENTORY
     *
     * @param string|callable $token
     * @param string $expectedExceptionName
     * @param string $expectedExceptionMessage
     *
     * @throws Throwable
     */
    public function testThrowsModelNotFoundException(
        $token,
        string $expectedExceptionName,
        string $expectedExceptionMessage): void
    {
        // Given the bulk download job was mistakenly removed
        $this->seeder->seed();

        // And I have some token
        $someToken = is_callable($token) ? $token($this->seeder) : $token;

        // And I have a queueable job
        $queueableJob = new ProcessDownloadJob($someToken);

        // Then I expect to see an specific exception to be thrown
        $this->expectException($expectedExceptionName);
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        // When I call handle method
        $queueableJob->handle(
            app(BulkDownloadRepositoryInterface::class),
            app(BulkDownloadJobServiceInterface::class),
            app(LoggerInterface::class)
        );
    }

    /**
     * @group DW
     * @group DW_BULK
     * @group DW_BULK_INVENTORY
     *
     * @throws Throwable
     */
    public function testWriteTheFileInDisk(): void
    {
        // Given I have a dealer

        // And I have a bulk download job
        $this->seeder->seed();

        $dealerId = $this->seeder->dealers[0]->dealer_id;

        // And I have a payload with valid configuration
        $payload = BulkDownloadPayload::from([
            'filename' => str_replace('.', '-', uniqid('inventory-' . date('Ymd'), true)) . '.pdf'
        ]);

        // And I've successfully created the service `BulkDownloadJobService`
        /** @var BulkDownloadJobService $service */
        $service = app(BulkDownloadJobServiceInterface::class);

        // And I've set up a bulk download job with a right payload
        $monitoredJob = $service->setup($dealerId, $payload);

        // And I have a queueable job
        $queueableJob = new ProcessDownloadJob($monitoredJob->token);

        // When I call handle method
        $queueableJob->handle(
            app(BulkDownloadRepositoryInterface::class),
            app(BulkDownloadJobServiceInterface::class),
            app(LoggerInterface::class)
        );

        // Then I expect to see an file with certain name to be stored in the disk
        Storage::disk('s3')->assertExists(FilesystemPdfExporter::RUNTIME_PREFIX . $monitoredJob->payload->filename);
    }

    /**
     * Examples of parameters, expected total and last page numbers, and first bulk download name.
     *
     * @return array<string, array>
     * @throws Exception
     */
    public function invalidConfigurationsProvider(): array
    {
        $nonExistingToken = Uuid::uuid4()->toString();
        $tokenForWithoutFilenameLambda = static function (MonitoredJobSeeder $seeder): string {
            // Given I have a dealer
            $dealerId = $seeder->dealers[0]->dealer_id;
            // And I have a payload without filename
            $invalidPayload = BulkDownloadPayload::from([]);

            // And I've successfully created the service `BulkDownloadJobService`
            /** @var BulkDownloadJobService $service */
            $service = app(BulkDownloadJobServiceInterface::class);

            // And I've set up a bulk download job with a wrong payload
            return $service->setup($dealerId, $invalidPayload)->token;
        };

        return [                   // callable $token, string $expectedExceptionName, string $expectedExceptionMessage
            'Non existing token'    => ['token' => $nonExistingToken, ModelNotFoundException::class, sprintf('No query results for model [%s] %s', BulkDownload::class, $nonExistingToken)],
            'Without a filename'    => ['token' => $tokenForWithoutFilenameLambda, InvalidArgumentException::class, 'This job has a payload without a filename']
        ];
    }
}
