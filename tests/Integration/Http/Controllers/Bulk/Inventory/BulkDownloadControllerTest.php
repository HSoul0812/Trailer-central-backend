<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Bulk\Inventory;

use App\Http\Controllers\v1\Bulk\Inventory\BulkDownloadController;
use App\Http\Requests\Bulk\Inventory\CreateBulkDownloadRequest;
use App\Jobs\Bulk\Inventory\ProcessDownloadJob;
use App\Models\Bulk\Inventory\BulkDownload;
use App\Repositories\Bulk\Inventory\BulkDownloadRepositoryInterface;
use App\Repositories\Bulk\Inventory\BulkDownloadRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Integration\AbstractMonitoredJobsTest;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Support\Facades\Bus;
use Ramsey\Uuid\Uuid;
use Exception;

/**
 * @covers \App\Http\Controllers\v1\Bulk\Inventory\BulkDownloadController
 * @group MonitoredJobs
 */
class BulkDownloadControllerTest extends AbstractMonitoredJobsTest
{
    /**
     * @dataProvider invalidParametersForCreationProvider
     *
     * @covers ::create
     *
     * @group DW
     * @group DW_BULK
     * @group DW_BULK_INVENTORY
     *
     * @param array $params
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @param string|null $firstExpectedErrorMessage
     * @throws Exception
     */
    public function testCreateFinancialsReportWithWrongParameters(array   $params,
                                                                  string  $expectedException,
                                                                  string  $expectedExceptionMessage,
                                                                  ?string $firstExpectedErrorMessage): void
    {
        $paramsExtracted = $this->seeder->extractValues($params);

        // Given I have a bad formed "CreateBulkDownloadRequest" request
        $request = new CreateBulkDownloadRequest($paramsExtracted);

        // And I'm using the controller "BulkDownloadController"
        $controller = app(BulkDownloadController::class);

        // Then I expect to see an specific exception to be thrown
        $this->expectException($expectedException);
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        try {
            // When I call the create action using the bad formed request
            $controller->create($request);
        } catch (ResourceException $exception) {
            // Then I should see that the first error message has a specific string
            self::assertSame($firstExpectedErrorMessage, $exception->getErrors()->first());

            throw $exception;
        }
    }

    /**
     * @dataProvider validParametersCreationProvider
     *
     * @covers ::create
     *
     * @group DW
     * @group DW_BULK
     * @group DW_BULK_INVENTORY
     *
     * @param array $params
     * @throws Exception
     */
    public function testCreateFinancialsReportWithValidParameters(array $params): void
    {
        // Given I have some inventory
        $this->seeder->seedDealers();

        // And I'm using the controller "BulkReportsController"
        $controller = app(BulkDownloadController::class);
        // And I have a well formed "CreateBulkReportRequest" request
        $request = new CreateBulkDownloadRequest($this->seeder->extractValues($params));

        Bus::fake();

        // When I call the create action using the well formed request
        $response = $controller->create($request);

        // Then I should see that job wit a specific name was enqueued
        Bus::assertDispatched(ProcessDownloadJob::class);
        // And I should see that response status is 202
        self::assertEquals(Response::HTTP_ACCEPTED, $response->status());
    }

    /**
     * @dataProvider invalidParametersForReadProvider
     *
     * @param array $params
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @param string|null $firstExpectedErrorMessage
     *
     * @covers ::read
     *
     * @group DW
     * @group DW_BULK
     * @group DW_BULK_INVENTORY
     *
     * @throws Exception
     */
    public function testReadReportWithInvalidParameters(array   $params,
                                                        string  $expectedException,
                                                        string  $expectedExceptionMessage,
                                                        ?string $firstExpectedErrorMessage): void
    {
        // Given I have few bulk download jobs
        $this->seeder->seed();

        // And I'm using the "BulkDownloadController" controller
        $controller = app(BulkDownloadController::class);
        // And I have a bad formed "CreateBulkDownloadRequest" request
        $request = new CreateBulkDownloadRequest($this->seeder->extractValues($params));

        // Then I expect to see an specific exception to be thrown
        $this->expectException($expectedException);
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        try {
            // When I call the read action using the token and the bad formed request
            $controller->read($request);
        } catch (ResourceException $exception) {
            // Then I should see that the first error message has a specific string
            self::assertSame($firstExpectedErrorMessage, $exception->getErrors()->first());

            throw $exception;
        }
    }

    /**
     * @dataProvider responseAccordingToJobStatusProvider
     *
     * @param array $params
     * @param string $jobStatus
     * @param int $expectedHttpCodeStatus
     * @param array $expectedPayloadResponse
     *
     * @covers ::read
     *
     * @group DW
     * @group DW_BULK
     * @group DW_BULK_INVENTORY
     */
    public function testReadReportWithDifferentResponseStatuses(array  $params,
                                                                string $jobStatus,
                                                                int    $expectedHttpCodeStatus,
                                                                array  $expectedPayloadResponse): void
    {
        /** @var BulkDownloadRepository $repository */
        $repository = app(BulkDownloadRepositoryInterface::class);

        /** @var array<string, float> $response */

        // Given I have few bulk download jobs
        $this->seeder->seed();

        // And I've picked one job

        $extractedParams = $this->seeder->extractValues($params);

        // And I know that my picked job has a specific status
        $repository->update(
            $extractedParams['token'],
            ['status' => $jobStatus, 'progress' => $expectedPayloadResponse['progress'] ?? 0]
        );

        // And I'm using the "BulkDownloadController" controller
        $controller = app(BulkDownloadController::class);
        // And I have a well formed "CreateBulkDownloadRequest" request
        $request = new CreateBulkDownloadRequest($extractedParams);

        // When I call the read action using the provided token and request
        $response = $controller->read($request);

        // Then I should see that response status is the same as expected
        self::assertSame($expectedHttpCodeStatus, $response->getStatusCode());

        if ($jobStatus === BulkDownload::STATUS_COMPLETED) {
            // And I should see that response is streamed and false
            self::assertFalse($response->getContent());
        } else {
            // And I should see that response has a specific structures as expected
            self::assertSame($expectedPayloadResponse, $response->original);
        }
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     * @throws Exception when Uuid::uuid4 cannot generate a uuid
     */
    public function responseAccordingToJobStatusProvider(): array
    {
        return [               // array $parameters, string $jobStatus, int $expectedHttpCodeStatus, array $expectedPayloadResponse
            'pending job' => [['dealer_id' => $this->getSeededData(0, 'id'), 'token' => $this->getSeededData(0, 'random-token')], BulkDownload::STATUS_PENDING, Response::HTTP_ACCEPTED, ['message' => 'It is pending', 'progress' => 0.0]],
            'processing job' => [['dealer_id' => $this->getSeededData(0, 'id'), 'token' => $this->getSeededData(0, 'random-token')], BulkDownload::STATUS_PROCESSING, Response::HTTP_OK, ['message' => 'Still processing', 'progress' => 50.0]],
            'failed job' => [['dealer_id' => $this->getSeededData(0, 'id'), 'token' => $this->getSeededData(0, 'random-token')], BulkDownload::STATUS_FAILED, Response::HTTP_INTERNAL_SERVER_ERROR, ['message' => 'This file could not be completed. Please request a new file.']],
            'completed job' => [['dealer_id' => $this->getSeededData(0, 'id'), 'token' => $this->getSeededData(0, 'random-token')], BulkDownload::STATUS_COMPLETED, Response::HTTP_OK, ['message' => 'Completed', 'progress' => 100.0]]
        ];
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     * @throws Exception when Uuid::uuid4 cannot generate a uuid
     */
    public function invalidParametersForReadProvider(): array
    {
        return [                 // array $parameters, string $expectedException, string $expectedExceptionMessage, string $firstExpectedErrorMessage
            'No token' => [[], ResourceException::class, 'Validation Failed', 'The token field is required.'],
            'Bad token' => [['dealer_id' => 666999, 'token' => 'this-is-a-token'], ResourceException::class, 'Validation Failed', 'The token must be a valid UUID.'],
            'Non-existent token' => [['dealer_id' => $this->getSeededData(0, 'id'), 'token' => Uuid::uuid4()->toString()], HttpException::class, 'Job not found', null]
        ];
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     * @throws Exception when Uuid::uuid4 cannot generate a uuid
     */
    public function invalidParametersForCreationProvider(): array
    {
        return [              // array $parameters, string $expectedException, string $expectedExceptionMessage, string $firstExpectedErrorMessage
            'No dealer' => [[], ResourceException::class, 'Validation Failed', 'The dealer id field is required.'],
            'Bad token' => [['dealer_id' => 666999, 'token' => 'this-is-a-token'], ResourceException::class, 'Validation Failed', 'The token must be a valid UUID.'],
            'Bad output type' => [['dealer_id' => 666999, 'output' => 'word'], ResourceException::class, 'Validation Failed', 'The selected output is invalid.'],
        ];
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     * @throws Exception when Uuid::uuid4 cannot generate a uuid
     */
    public function validParametersCreationProvider(): array
    {
        return [           // array $parameters
            'With token' => [['dealer_id' => $this->getSeededData(1, 'id'), 'token' => Uuid::uuid4()->toString()]]
        ];
    }
}
