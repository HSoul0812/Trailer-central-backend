<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Import\Parts\CsvImportService;

use App\Models\Bulk\Parts\BulkUpload;
use App\Services\Import\Parts\CsvImportService;
use Illuminate\Support\Facades\Log;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Ramsey\Uuid\Uuid;
use Mockery;
use RuntimeException;
use Exception;
use Tests\TestCase;

/**
 * @covers \App\Services\Import\Parts\CsvImportService::run
 * @group MonitoredJobs
 */
class RunTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testWithNotSetUpBulkUpload(): void
    {
        // Given I have the three dependencies for "CsvImportService" creation
        $dependencies = new CsvImportServiceDependencies();
        // And I have a "CsvImportService" properly created
        $service = new CsvImportService(
            $dependencies->bulkUploadRepository,
            $dependencies->partsRepository,
            $dependencies->binRepository
        );

        // Then I expect to see an specific exception to be thrown
        $this->expectException(RuntimeException::class);
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage('"bulkUpload" has not been set up');

        // When I call the run method
        $service->run();
    }

    /**
     * Test that it will return a false value because it has caught an exception
     *
     * @throws Exception
     */
    public function testWillFailBecauseAnException(): void
    {
        /** @var MockInterface|LegacyMockInterface|CsvImportService $service */

        // Given I have the three required dependencies for "CsvImportService" creation
        $dependencies = new CsvImportServiceDependencies();
        // And I have a specific token
        $token = Uuid::uuid4()->toString();
        // And I have an expected list of validation errors
        $validationErrors = [];
        // And I know that it should throw an exception
        $exception = new Exception('This a dummy exception', 500);

        // Then I expect that repository update method is called with certain arguments and will return true
        $dependencies->bulkUploadRepository
            ->shouldReceive('update')
            ->once()
            ->with($token, [
                'status' => BulkUpload::STATUS_FAILED,
                'result' => [
                    'validation_errors' => $validationErrors,
                    'status' => BulkUpload::EXCEPTION_ERROR,
                    'exception' => [
                        'message' => $exception->getMessage(),
                        'code' => $exception->getCode(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine()
                    ]
                ]
            ])
            ->andReturn(true);

        // Also given I have a "BulkUpload" properly created with a specific token
        $bulkUpload = Mockery::mock(BulkUpload::class);
        $bulkUpload->shouldReceive('getAttribute')->with('token')->andReturn($token);

        // And I have a "CsvImportService" properly created
        $service = Mockery::mock(CsvImportService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $service->setBulkUpload($bulkUpload);

        // Then I expect that validate method is called once and will throw an exception
        $service->shouldReceive('validate')->once()->andThrow($exception);
        // And I expect that outputValidationErrors method is called once and return certainly list of errors
        $service->shouldReceive('outputValidationErrors')->once()->andReturn($validationErrors);
        // And I expect that two log entries are stored
        Log::shouldReceive('info')->twice();

        // When I call the run method
        $result = $service->run();

        // Then I expect to receive a false value
        self::assertFalse($result);
    }

    /**
     * Test that it will return a false value because the validation has failed
     *
     * @throws Exception
     */
    public function testWillFailBecauseValidation(): void
    {
        /** @var MockInterface|LegacyMockInterface|CsvImportService $service */

        // Given I have the three required dependencies for "CsvImportService" creation
        $dependencies = new CsvImportServiceDependencies();
        // And I have a specific token
        $token = Uuid::uuid4()->toString();
        // And I have an expected list of validation errors
        $validationErrors = ['There was an error in the line 123', 'The column XXX is required'];

        // Then I expect that repository update method is called with certain arguments and will return true
        $dependencies->bulkUploadRepository
            ->shouldReceive('update')
            ->once()
            ->with($token, [
                'status' => BulkUpload::STATUS_FAILED,
                'result' => ['validation_errors' => $validationErrors, 'status' => BulkUpload::VALIDATION_ERROR]
            ])
            ->andReturn(true);

        // Also given I have a "BulkUpload" properly created with a specific token
        $bulkUpload = Mockery::mock(BulkUpload::class);
        $bulkUpload->shouldReceive('getAttribute')->with('token')->andReturn($token);

        // And I have a "CsvImportService" properly created
        $service = Mockery::mock(CsvImportService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $service->setBulkUpload($bulkUpload);

        // Then I expect that validate method is called once and return false because it has failed
        $service->shouldReceive('validate')->once()->andReturnFalse();
        // And I expect that outputValidationErrors method is called once and return certain list of errors
        $service->shouldReceive('outputValidationErrors')->once()->andReturn($validationErrors);
        // And I expect that two log entries are stored
        Log::shouldReceive('info')->twice();

        // When I call the run method
        $result = $service->run();

        // Then I expect to receive a false value
        self::assertFalse($result);
    }

    /**
     * @throws Exception
     */
    public function testWillNotFail(): void
    {
        /** @var MockInterface|LegacyMockInterface|CsvImportService $service */

        // Given I have the three required dependencies for "CsvImportService" creation
        $dependencies = new CsvImportServiceDependencies();
        // And I have a specific token
        $token = Uuid::uuid4()->toString();
        // And I have a "BulkUpload" properly created with a specific token
        $bulkUpload = Mockery::mock(BulkUpload::class);
        $bulkUpload->shouldReceive('getAttribute')->with('token')->andReturn($token);
        // And I have a "CsvImportService" properly created
        $service = Mockery::mock(CsvImportService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $service->setBulkUpload($bulkUpload);

        // Then I expect that validate method is called once and return false because it has passed its validations rules
        $service->shouldReceive('validate')->once()->andReturnTrue();
        // And I expect that two log entries are stored
        Log::shouldReceive('info')->twice();
        // And I expect that import method is called once
        $service->shouldReceive('import')->once()->andReturnTrue();

        // When I call the run method
        $result = $service->run();

        // Then I expect to receive a true value
        self::assertTrue($result);
    }
}
