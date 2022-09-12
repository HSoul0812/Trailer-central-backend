<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Export\Parts\BulkReportJobService;

use App\Exceptions\Common\UndefinedReportTypeException;
use App\Services\Export\FilesystemPdfExporter;
use App\Services\Export\Parts\BulkReportJobService;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Bulk\Parts\BulkReport;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Throwable;
use Mockery;

/**
 * @covers \App\Services\Export\Parts\BulkReportJobService::run
 * @group MonitoredJobs
 */
class RunTest extends TestCase
{
    use WithFaker;

    /**
     * @group DMS
     * @group DMS_BULK_REPORT
     *
     * @throws Throwable  when Uuid::uuid4()->toString() could not generate a uuid
     */
    public function testWillThrowAnExceptionBecauseGetData(): void
    {
        /** @var  MockInterface|LegacyMockInterface|BulkReportJobService $service */

        // Given I have the four dependencies for "BulkReportJobServiceDependencies" creation
        $dependencies = new BulkReportJobServiceDependencies();
        // And I have a "BulkReport" model with a wrong type report
        $token = Uuid::uuid4()->toString();
        $job = new BulkReport([
            'dealer_id' => $this->faker->unique()->numberBetween(100, 50000),
            'token' => $token,
            'payload' => [
                'filename' => str_replace('.', '-', uniqid('financials-parts-' . date('Ymd'), true)) . '.pdf',
                'type' => 'whatever' // wrong type report
            ],
            'queue' => BulkReport::QUEUE_NAME,
            'concurrency_level' => BulkReport::LEVEL_DEFAULT,
            'name' => BulkReport::QUEUE_JOB_NAME
        ]);
        // And I know what exception will be thrown
        $exception = new UndefinedReportTypeException("There is not a '{$job->payload->type}' report type defined", 500);

        // Then I expect that a log entry is stored
        $dependencies->loggerService->shouldReceive('info')->once();
        // And I expect that bulk repository "updateProgress" method is called once with certain arguments returning true
        $dependencies->bulkReportRepository
            ->shouldReceive('updateProgress')
            ->once()
            ->with($job->token, 0)
            ->andReturnTrue();
        // And I expect that bulk repository "setFailed" method is called once with certain arguments returning true
        $dependencies->bulkReportRepository
            ->shouldReceive('setFailed')
            ->once()
            ->with($job->token, ['message' => "Got exception: {$exception->getMessage()}"])
            ->andReturnTrue();
        // And I expect that an error log entry is stored
        $dependencies->loggerService->shouldReceive('error')->once();

        // Also I have a "BulkReportJobService" properly created
        $service = Mockery::mock(BulkReportJobService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // Then I expect that "getData" is called once and will throw a known exception
        $service->shouldReceive('getData')->with($job)->once()->andThrow($exception);
        // And I expect to see an specific exception to be thrown
        $this->expectException(get_class($exception));
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage($exception->getMessage());

        // When I call the run method
        $service->run($job);
    }

    /**
     * @group DMS
     * @group DMS_BULK_REPORT
     *
     * @throws Throwable  when Uuid::uuid4()->toString() could not generate a uuid
     */
    public function testWillThrowAnExceptionBecauseGetExporter(): void
    {
        /** @var  MockInterface|LegacyMockInterface|BulkReportJobService $service */

        // Given I have the four dependencies for "BulkReportJobServiceDependencies" creation
        $dependencies = new BulkReportJobServiceDependencies();
        // And I have a "BulkReport" model without a filename
        $token = Uuid::uuid4()->toString();
        $job = new BulkReport([
            'dealer_id' => $this->faker->unique()->numberBetween(100, 50000),
            'token' => $token,
            'payload' => [
                // without a filename
                'type' => BulkReport::TYPE_FINANCIALS
            ],
            'queue' => BulkReport::QUEUE_NAME,
            'concurrency_level' => BulkReport::LEVEL_DEFAULT,
            'name' => BulkReport::QUEUE_JOB_NAME
        ]);
        // And I know what exception will be thrown
        $exception = new InvalidArgumentException('This job has a payload without a filename', 500);

        // Then I expect that a log entry is stored
        $dependencies->loggerService->shouldReceive('info')->once();
        // And I expect that bulk repository "updateProgress" method is called twice with certain arguments returning true
        $dependencies->bulkReportRepository
            ->shouldReceive('updateProgress')
            ->twice()
            ->andReturnTrue();
        // And I expect that bulk repository "setFailed" method is called with certain arguments returning true
        $dependencies->bulkReportRepository
            ->shouldReceive('setFailed')
            ->once()
            ->with($job->token, ['message' => "Got exception: {$exception->getMessage()}"])
            ->andReturnTrue();
        // And I expect that an error log entry is stored
        $dependencies->loggerService->shouldReceive('error')->once();

        // Also I have a "BulkReportJobService" properly created
        $service = Mockery::mock(BulkReportJobService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // Then I expect that "getData" is called once and will return a known data
        $service->shouldReceive('getData')->with($job)->once()->andReturn([]);
        // And I expect that "getData" is called once and will throw a known exception
        $service->shouldReceive('getExporter')->with($job)->once()->andThrow($exception);
        // And I expect to see an specific exception to be thrown
        $this->expectException(get_class($exception));
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage($exception->getMessage());

        // When I call the run method
        $service->run($job);
    }

    /**
     * @group DMS
     * @group DMS_BULK_REPORT
     *
     * @throws Throwable  when Uuid::uuid4()->toString() could not generate a uuid
     */
    public function testWillExportTheFile(): void
    {
        /** @var  MockInterface|LegacyMockInterface|BulkReportJobService $service */

        // Given I have the four dependencies for "BulkReportJobServiceDependencies" creation
        $dependencies = new BulkReportJobServiceDependencies();
        // And I have a "BulkReport" model without a filename
        $token = Uuid::uuid4()->toString();
        $job = new BulkReport([
            'dealer_id' => $this->faker->unique()->numberBetween(100, 50000),
            'token' => $token,
            'payload' => [
                'filename' => str_replace('.', '-', uniqid('financials-parts-' . date('Ymd'), true)) . '.pdf',
                'type' => BulkReport::TYPE_FINANCIALS
            ],
            'queue' => BulkReport::QUEUE_NAME,
            'concurrency_level' => BulkReport::LEVEL_DEFAULT,
            'name' => BulkReport::QUEUE_JOB_NAME
        ]);

        // Then I expect that a log entry is stored
        $dependencies->loggerService->shouldReceive('info')->twice();
        // And I expect that bulk repository "updateProgress" method is called twice with certain arguments returning true
        $dependencies->bulkReportRepository
            ->shouldReceive('updateProgress')
            ->twice()
            ->andReturnTrue();
        // And I expect that bulk repository "setCompleted" method is called with certain arguments returning true
        $dependencies->bulkReportRepository
            ->shouldReceive('setCompleted')
            ->once()
            ->with($job->token)
            ->andReturnTrue();

        // Also I have a "BulkReportJobService" properly created
        $service = Mockery::mock(BulkReportJobService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // And I have a "FilesystemPdfExporter" properly created
        $fileStorage = Mockery::mock(
            FilesystemPdfExporter::class,
            [Storage::fake('tmp'), $job->payload->filename]
        )
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $fileStorage->shouldReceive('export')->once();

        // Then I expect that "getData" is called once and will return a known data
        $service->shouldReceive('getData')->with($job)->once()->andReturn([]);
        // And I expect that "getExporter" is called once and will return a known file exporter
        $service->shouldReceive('getExporter')->with($job)->once()->andReturn($fileStorage);

        // When I call the run method
        $service->run($job);
    }
}
