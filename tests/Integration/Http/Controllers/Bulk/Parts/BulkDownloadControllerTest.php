<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Bulk\Parts;

use App\Exceptions\Common\BusyJobException;
use App\Http\Controllers\v1\Bulk\Parts\BulkDownloadController;
use App\Http\Requests\Bulk\Parts\CreateBulkDownloadRequest;
use App\Http\Requests\Jobs\ReadMonitoredJobsRequest;
use App\Jobs\Bulk\Parts\CsvExportJob;
use App\Models\Common\MonitoredJob;
use App\Repositories\Bulk\Parts\BulkDownloadRepository;
use App\Repositories\Bulk\Parts\BulkDownloadRepositoryInterface;
use Dingo\Api\Exception\ResourceException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Bus;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Integration\AbstractMonitoredJobsTest;

/**
 * @covers \App\Http\Controllers\v1\Bulk\Parts\BulkDownloadController
 * @group MonitoredJobs
 */
class BulkDownloadControllerTest extends AbstractMonitoredJobsTest
{
    /**
     * @dataProvider validParametersCreationProvider
     *
     * @covers ::create
     *
     * @group DMS
     * @group DMS_BULK
     * @group DMS_BULK_DOWNLOAD
     *
     * @param array $params
     *
     * @throws BusyJobException
     */
    public function testCreateWithValidParameters(array $params): void
    {
        // Given I have few dealers
        $this->seeder->seedDealers();

        // And I'm using the controller "BulkDownloadController"
        $controller = app(BulkDownloadController::class);
        // And I have a well formed "CreateBulkDownloadRequest" request
        $request = new CreateBulkDownloadRequest($this->seeder->extractValues($params));

        Bus::fake();

        // When I call the create action using the well formed request
        $response = $controller->create($request);

        // Then I should see that job wit a specific name was enqueued
        Bus::assertDispatched(CsvExportJob::class);
        // And I should see that response status is 202
        self::assertEquals(JsonResponse::HTTP_ACCEPTED, $response->status());
    }

    /**
     * Test that when the user has provided the `wait` parameter, then then action controller will wait, but since
     * there is not job working on it, it will response a 500 http status code
     *
     * @covers ::create
     *
     * @group DMS
     * @group DMS_BULK
     * @group DMS_BULK_DOWNLOAD
     *
     * @throws BusyJobException
     */
    public function testCreateWithWaitParameter(): void
    {
        // Given I have few dealers
        $this->seeder->seedDealers();

        // And I'm using the controller "BulkDownloadController"
        $controller = app(BulkDownloadController::class);
        // And I have a well formed "CreateBulkDownloadRequest" request with wait parameter
        $request = new CreateBulkDownloadRequest(['dealer_id' => $this->seeder->dealers[0]->dealer_id, 'wait' => 1]);

        Bus::fake();
        // When I call the create action using the well formed request
        $response = $controller->create($request);

        // Then I should see that job wit a specific name was enqueued
        Bus::assertDispatched(CsvExportJob::class);
        // And I should see that response status is 200
        self::assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        // And I should see that response is a stream
        self::assertInstanceOf(StreamedResponse::class, $response);
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
     * @group DMS
     * @group DMS_BULK
     * @group DMS_BULK_DOWNLOAD
     */
    public function testReadWithInvalidParameters(array $params,
                                                  string $expectedException,
                                                  string $expectedExceptionMessage,
                                                  ?string $firstExpectedErrorMessage): void
    {
        // Given I have few monitored jobs
        $this->seeder->seed();

        // And I'm using the "BulkDownloadController" controller
        $controller = app(BulkDownloadController::class);
        // And I have a bad formed "GetMonitoredJobsRequest" request
        $request = new ReadMonitoredJobsRequest($this->seeder->extractValues($params));

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
     * @covers ::status
     *
     * @group DMS
     * @group DMS_BULK
     * @group DMS_BULK_DOWNLOAD
     */
    public function testReadWithDifferentResponseStatuses(array $params,
                                                          string $jobStatus,
                                                          int $expectedHttpCodeStatus,
                                                          array $expectedPayloadResponse): void
    {
        /** @var BulkDownloadRepository $repository */
        $repository = app(BulkDownloadRepositoryInterface::class);

        /** @var array<string, float> $response */

        // Given I have few monitored jobs
        $this->seeder->seed();
        // And I've picked one job

        $extractedParams = $this->seeder->extractValues($params);
        // And I know that my picked job has a specific status
        $repository->update(
            $extractedParams['token'],
            ['status' => $jobStatus, 'progress' => $expectedPayloadResponse['progress'] ?? 0]
        );

        // And I'm using the "MonitoredJobsController" controller
        $controller = app(BulkDownloadController::class);
        // And I have a well formed "GetMonitoredJobsRequest" request
        $request = new ReadMonitoredJobsRequest($extractedParams);

        // When I call the status action using the provided token and request
        $response = $controller->read($request);

        // Then I should see that response status is the same as expected
        self::assertSame($expectedHttpCodeStatus, $response->getStatusCode());

        if ($jobStatus === MonitoredJob::STATUS_COMPLETED) {
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
            'pending job'    => [['dealer_id' => $this->getSeededData(0,'id'), 'token' => $this->getSeededData(0,'random-token')], MonitoredJob::STATUS_PENDING, JsonResponse::HTTP_ACCEPTED, ['message' => 'It is pending', 'progress' => 0.0]],
            'processing job' => [['dealer_id' => $this->getSeededData(0,'id'), 'token' => $this->getSeededData(0,'random-token')], MonitoredJob::STATUS_PROCESSING, JsonResponse::HTTP_OK, ['message' => 'Still processing', 'progress' => 50.0]],
            'failed job'     => [['dealer_id' => $this->getSeededData(0,'id'), 'token' => $this->getSeededData(0,'random-token')], MonitoredJob::STATUS_FAILED, JsonResponse::HTTP_INTERNAL_SERVER_ERROR, ['message' => 'This file could not be completed. Please request a new file.']],
            'completed job'  => [['dealer_id' => $this->getSeededData(0,'id'), 'token' => $this->getSeededData(0,'random-token')], MonitoredJob::STATUS_COMPLETED, JsonResponse::HTTP_OK, ['message' => 'Completed', 'progress' => 100.0]]
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
        return [                     // array $parameters, string $expectedException, string $expectedExceptionMessage, string $firstExpectedErrorMessage
            'No token'           => [[], ResourceException::class, 'Validation Failed', 'The token field is required.'],
            'Bad token'          => [['token' => 'this-is-a-token'], ResourceException::class, 'Validation Failed', 'The token must be a valid UUID.'],
            'Non-existent token' => [['token' => Uuid::uuid4()->toString()], HttpException::class, 'Job not found', null]
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
        return [                                            // array $parameters, string $expectedException, string $expectedExceptionMessage, string $firstExpectedErrorMessage
            'No dealer'                                     => [[], ResourceException::class, 'Validation Failed', 'The dealer id field is required.'],
            'Bad token'                                     => [['dealer_id' => 666999, 'token' => 'this-is-a-token'], ResourceException::class, 'Validation Failed', 'The token must be a valid UUID.'],
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
            'No token'   => [['dealer_id' => $this->getSeededData(0,'id')]],
            'With token' => [['dealer_id' => $this->getSeededData(1,'id'), 'token' => Uuid::uuid4()->toString()]]
        ];
    }
}
