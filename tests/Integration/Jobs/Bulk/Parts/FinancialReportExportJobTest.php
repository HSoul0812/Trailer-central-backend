<?php

declare(strict_types=1);

namespace Tests\Integration\Jobs\Bulk\Parts;

use App\Exceptions\Common\UndefinedReportTypeException;
use App\Jobs\Bulk\Parts\FinancialReportExportJob;
use App\Models\Bulk\Parts\BulkReport;
use App\Models\Bulk\Parts\BulkReportPayload;
use App\Repositories\Bulk\Parts\BulkReportRepositoryInterface;
use App\Services\Export\FilesystemPdfExporter;
use App\Services\Export\Parts\BulkReportJobService;
use App\Services\Export\Parts\BulkReportJobServiceInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Tests\database\seeds\Common\MonitoredJobSeeder;
use Tests\Integration\AbstractMonitoredJobsTest;
use Throwable;

/**
 * @covers \App\Jobs\Bulk\Parts\FinancialReportExportJob::handle
 * @group MonitoredJobs
 */
class FinancialReportExportJobTest extends AbstractMonitoredJobsTest
{
    /**
     * @dataProvider invalidConfigurationsProvider
     *
     * @group DMS
     * @group DMS_BULK_REPORT
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
        // Given I dont have any monitored job
        $this->seeder->seed();

        // And I have some token
        $someToken = is_callable($token) ? $token($this->seeder) : $token;

        // And I have a queueable job
        $queueableJob = new FinancialReportExportJob($someToken);

        // Then I expect to see an specific exception to be thrown
        $this->expectException($expectedExceptionName);
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        // When I call handle method
        $queueableJob->handle(app(BulkReportRepositoryInterface::class), app(BulkReportJobServiceInterface::class));
    }

    /**
     * @group DMS
     * @group DMS_BULK_REPORT
     *
     * @throws Throwable
     */
    public function testWriteTheFileOnS3(): void
    {
        Storage::fake('s3');

        // Given I dont have any monitored job
        $this->seeder->seed();

        // Given I have a dealer
        $dealerId = $this->seeder->dealers[0]->dealer_id;

        // And I have a payload without filename
        $payload = BulkReportPayload::from([
            'filename' => str_replace('.', '-', uniqid('financials-parts-' . date('Ymd'), true)) . '.pdf',
            'type' => BulkReport::TYPE_FINANCIALS
        ]);

        // And I've successfully created the service `BulkReportJobService`
        /** @var BulkReportJobService $service */
        $service = app(BulkReportJobServiceInterface::class);

        // And I've set up a monitored job with a right payload
        $monitoredJob = $service->setup($dealerId, $payload);

        // And I have a queueable job
        $queueableJob = new FinancialReportExportJob($monitoredJob->token);

        // When I call handle method
        $queueableJob->handle(app(BulkReportRepositoryInterface::class), app(BulkReportJobServiceInterface::class));

        Storage::disk('s3')->assertExists(FilesystemPdfExporter::RUNTIME_PREFIX . $monitoredJob->payload->filename);
    }

    /**
     * Examples of parameters, expected total and last page numbers, and first monitored job name.
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
            $invalidPayload = BulkReportPayload::from(['type' => BulkReport::TYPE_FINANCIALS]);

            // And I've successfully created the service `BulkReportJobService`
            /** @var BulkReportJobService $service */
            $service = app(BulkReportJobServiceInterface::class);

            // And I've set up a monitored job with a wrong payload
            return $service->setup($dealerId, $invalidPayload)->token;
        };

        return [                   // callable $token, string $expectedExceptionName, string $expectedExceptionMessage
            'Non existing token'    => ['token' => $nonExistingToken, ModelNotFoundException::class, sprintf('No query results for model [%s] %s', BulkReport::class, $nonExistingToken)],
            'Undefined type report' => ['token' => $this->getSeededData(0, 'random-token'), UndefinedReportTypeException::class, "There is not a '' report type defined"],
            'Without a filename'    => ['token' => $tokenForWithoutFilenameLambda, InvalidArgumentException::class, 'This job has a payload without a filename']
        ];
    }
}
