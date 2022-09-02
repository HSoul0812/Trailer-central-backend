<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Jobs;

use App\Http\Controllers\v1\Jobs\MonitoredJobsController;
use App\Http\Requests\Jobs\GetMonitoredJobsRequest;
use App\Models\Common\MonitoredJob;
use App\Repositories\Common\MonitoredJobRepository;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use Dingo\Api\Exception\ResourceException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Ramsey\Uuid\Uuid;
use Tests\Integration\AbstractMonitoredJobsTest;

/**
 * @covers \App\Http\Controllers\v1\Jobs\MonitoredJobsController
 * @group MonitoredJobs
 */
class MonitoredJobsControllerTest extends AbstractMonitoredJobsTest
{
    /**
     * @covers ::index
     *
     * @group DMS
     * @group DMS_MONITORED_JOBS
     */
    public function testIndexWithInvalidParameter(): void
    {
        // Given I'm using the controller "MonitoredJobsController"
        $controller = app(MonitoredJobsController::class);
        // And I have a bad formed "GetMonitoredJobsRequest"
        $request = new GetMonitoredJobsRequest([]);

        // Then I expect to see an "ResourceException" to be thrown
        $this->expectException(ResourceException::class);
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage('Validation Failed');

        try {
            // When I call the index action using the bad formed request
            $controller->index($request);
        } catch (ResourceException $exception) {
            // Then I should see that the first error message has a specific string
            self::assertSame('The dealer id field is required.', $exception->getErrors()->first());

            throw $exception;
        }
    }

    /**
     * @dataProvider validParametersProvider
     *
     * @param array $params
     * @param callable $expectedTotal callable(): int
     * @param int $expectedLastPage
     * @param callable $expectedJobs callable(): \Illuminate\Support\Collection<MonitoredJob>
     *
     * @covers ::index
     *
     * @group DMS
     * @group DMS_MONITORED_JOBS
     */
    public function testIndexListJobsPerDealer(array $params,
                                          callable $expectedTotal,
                                          int $expectedLastPage,
                                          callable $expectedJobs): void
    {
        /** @var LengthAwarePaginator $paginator */

        // Given I have few monitored jobs which belongs to a specific dealer
        $this->seeder->seed();
        // And I'm using the "MonitoredJobsController" controller
        $controller = app(MonitoredJobsController::class);
        // And I have a well formed "GetMonitoredJobsRequest" request
        $request = new GetMonitoredJobsRequest($this->seeder->extractValues($params));

        // When I call the index action using the well formed request
        $response = $controller->index($request);
        $paginator = $response->original;

        // Then I should see that response status is 200
        self::assertSame(JsonResponse::HTTP_OK, $response->status());
        // And I should see that the expected total of monitored jobs is the same as monitored jobs retrieved
        self::assertSame($expectedTotal($this->seeder), $paginator->total());
    }

    /**
     * @dataProvider invalidParametersProvider
     *
     * @param array $params
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @param string $firstExpectedErrorMessage
     *
     * @covers ::statusByToken
     * @covers ::status
     *
     * @group DMS
     * @group DMS_MONITORED_JOBS
     */
    public function testStatusByTokenWithInvalidParameters(array $params,
                                                    string $expectedException,
                                                    string $expectedExceptionMessage,
                                                    string $firstExpectedErrorMessage): void
    {
        // Given I have few monitored jobs
        $this->seeder->seed();

        // And I'm using the "MonitoredJobsController" controller
        $controller = app(MonitoredJobsController::class);
        // And I have a bad formed "GetMonitoredJobsRequest" request
        $request = new GetMonitoredJobsRequest($this->seeder->extractValues($params));

        // Then I expect to see an specific exception to be thrown
        $this->expectException($expectedException);
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        try {
            // When I call the status action using the token and the bad formed request
            $controller->statusByToken($request->get('token', ''), $request);
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
     * @covers ::statusByToken
     * @covers ::status
     *
     * @group DMS
     * @group DMS_MONITORED_JOBS
     */
    public function testStatusByTokenWithValidParameters(array $params,
                                                  string $jobStatus,
                                                  int $expectedHttpCodeStatus,
                                                  array $expectedPayloadResponse): void
    {
        /** @var MonitoredJobRepository $repository */
        $repository = app(MonitoredJobRepositoryInterface::class);

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
        $controller = app(MonitoredJobsController::class);
        // And I have a well formed "GetMonitoredJobsRequest" request
        $request = new GetMonitoredJobsRequest($extractedParams);

        // When I call the status action using the provided token and request
        $response = $controller->statusByToken($extractedParams['token'], $request);

        // Then I should see that response status is the same as expected
        self::assertSame($expectedHttpCodeStatus, $response->status());
        // And I should see that response has a specific structures as expected
        self::assertSame($expectedPayloadResponse, $response->original);
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
            'pending job'    => [['dealer_id' => $this->getSeededData(0,'id'), 'token' => $this->getSeededData(0,'random-token')], MonitoredJob::STATUS_PENDING, JsonResponse::HTTP_OK, ['message' => 'It is pending', 'progress' => 0.0]],
            'processing job' => [['dealer_id' => $this->getSeededData(0,'id'), 'token' => $this->getSeededData(0,'random-token')], MonitoredJob::STATUS_PROCESSING, JsonResponse::HTTP_OK, ['message' => 'Still processing', 'progress' => 50.0]],
            'completed job'  => [['dealer_id' => $this->getSeededData(0,'id'), 'token' => $this->getSeededData(0,'random-token')], MonitoredJob::STATUS_COMPLETED, JsonResponse::HTTP_OK, ['message' => 'Completed', 'progress' => 100.0]],
            'failed job'     => [['dealer_id' => $this->getSeededData(0,'id'), 'token' => $this->getSeededData(0,'random-token')], MonitoredJob::STATUS_FAILED, JsonResponse::HTTP_INTERNAL_SERVER_ERROR, ['message' => 'This process could not be completed. Please request a new job.']]
        ];
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     * @throws Exception when Uuid::uuid4 cannot generate a uuid
     */
    public function invalidParametersProvider(): array
    {
        return [                                                   // array $parameters, string $expectedException, string $expectedExceptionMessage, string $firstExpectedErrorMessage
            'No dealer'                                            => [[], ResourceException::class, 'Validation Failed', 'The dealer id field is required.'],
            'Bad token'                                            => [['dealer_id' => 666999, 'token' => 'this-is-a-token'], ResourceException::class, 'Validation Failed', 'The token must be a valid UUID.'],
            'Non-existent token'                                   => [['dealer_id' => $this->getSeededData(0,'id'), 'token' => Uuid::uuid4()->toString()], ResourceException::class, 'Validation Failed', 'The job was not found.'],
            'A token which does not belong to the provided dealer' => [['dealer_id' => $this->getSeededData(0,'id'), 'token' => $this->getSeededData(1,'random-token')], ResourceException::class, 'Validation Failed', 'The job was not found.']
        ];
    }

    /**
     * Examples of valid query parameter with their respective expected number of records and pages
     *
     * @return array<string, array>
     */
    public function validParametersProvider(): array
    {
        return [                                      // array $parameters, callable:int $expectedTotal, int $expectedLastPage, callable:Collection<MonitoredJob> $expectedJobs
            'By dummy dealer paged by 2'           => [['dealer_id' => $this->getSeededData(0,'id'), 'per_page' => 2], $this->getSeededData(0,'total'), 4, $this->getSeededData(0, 'jobs')],
            'By another dummy dealer paged by 100' => [['dealer_id' => $this->getSeededData(1, 'id')], $this->getSeededData(1,'total'), 1, $this->getSeededData(1, 'jobs')]
        ];
    }
}
