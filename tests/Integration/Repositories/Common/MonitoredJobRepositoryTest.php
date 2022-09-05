<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\Common;

use Illuminate\Contracts\Container\BindingResolutionException;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Common\MonitoredJobRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\Integration\AbstractMonitoredJobsTest;
use App\Models\Common\MonitoredJobResult;
use App\Models\Common\MonitoredJob;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use Exception;

/**
 * @covers \App\Repositories\Common\MonitoredJobRepository
 * @group MonitoredJobs
 */
class MonitoredJobRepositoryTest extends AbstractMonitoredJobsTest
{
    /**
     * Test that SUT is properly bound by the application
     *
     * @group DMS
     * @group DMS_MONITORED_JOBS
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     * @note IntegrationTestCase
     */
    public function testIoCForTheRepositoryInterfaceIsWorking(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(MonitoredJobRepository::class, $concreteRepository);
    }

    /**
     * @covers ::get
     * @group DMS
     * @group DMS_MONITORED_JOBS
     *
     * @throws BindingResolutionException
     * @throws Exception when Uuid::uuid4()->toString() could not generate a uuid
     */
    public function testGetIsWorkingProperly(): void
    {
        // Given I have few monitored jobs
        $this->seeder->seed();

        // And I've picked one monitored job
        /** @var MonitoredJob $randomJob */
        $randomJob = $this->getSeededData(0, 'random-job')($this->seeder);

        // And I've got the concrete repository from the IoC for "MonitoredJobRepositoryInterface"
        $repository = $this->getConcreteRepository();

        // When I call the method `get` with certain parameters that I know it will return a model
        $job = $repository->get(['token' => $randomJob->token]);

        // Then I should see that my expected token is the same retrieved from method `get`
        self::assertSame($randomJob->token, $job->token);

        // Also, when I call the method `get` with certain parameters that I know it will not return a model
        $job = $repository->get(['token' => Uuid::uuid4()->toString()]);

        // Then I should see that my expected null value was retrieved from method `get`
        self::assertNull($job);
    }

    /**
     * @covers ::findByToken
     * @group DMS
     * @group DMS_MONITORED_JOBS
     *
     * @throws BindingResolutionException
     * @throws Exception when Uuid::uuid4()->toString() could not generate a uuid
     */
    public function testFindByTokenIsWorkingProperly(): void
    {
        // Given I have few monitored jobs
        $this->seeder->seed();

        // And I've picked one token form the monitored jobs
        /** @var string $randomToken */
        $randomToken = $this->getSeededData(0, 'random-token')($this->seeder);

        // And I've got the concrete repository from the IoC for "MonitoredJobRepositoryInterface"
        // When I call the method `findByToken` with a existing token
        $job = $this->getConcreteRepository()->findByToken($randomToken);

        // Then I should see that the value retrieved from method `get` is not null
        self::assertNotNull($job);

        // Also, given I have a non existing token
        $nonExistingToken = Uuid::uuid4()->toString();
        // When I call the method `findByToken` with a existing token
        $job = $this->getConcreteRepository()->findByToken($nonExistingToken);
        // Then I should see that the value retrieved from method `get` is null
        self::assertNull($job);
    }

    /**
     * @covers ::getAll
     * @group DMS
     * @group DMS_MONITORED_JOBS
     *
     * @dataProvider queryParametersAndSummariesProvider
     *
     * @param array $params list of query parameters
     * @param int $expectedTotal
     * @param int $expectedLastPage
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     */
    public function testGetAllIsPaginatingAndFilteringAsExpected(
        array $params,
        int $expectedTotal,
        int $expectedLastPage
    ): void
    {
        // Given I have few monitored jobs
        $this->seeder->seed();

        // And I've got the concrete repository from the IoC for "MonitoredJobRepositoryInterface"
        // When I call the method `getAll` with a some valid parameters
        /** @var LengthAwarePaginator $monitoredJobs */
        $monitoredJobs = $this->getConcreteRepository()->getAll($this->seeder->extractValues($params));

        // Then I should see that instance type of the return value is `LengthAwarePaginator`
        self::assertInstanceOf(LengthAwarePaginator::class, $monitoredJobs);
        // And I should see that expected total of records is same as retrieved from `getAll`
        self::assertSame($expectedTotal, $monitoredJobs->total());
        // And I should see that expected last page number is same as retrieved from `getAll`
        self::assertSame($expectedLastPage, $monitoredJobs->lastPage());
    }

    /**
     * @covers ::update
     * @group DMS
     * @group DMS_MONITORED_JOBS
     *
     * @throws BindingResolutionException
     * @throws Exception when Uuid::uuid4()->toString() could not generate a uuid
     */
    public function testUpdateIsWorkingProperly(): void
    {
        // Given I have few monitored jobs
        $this->seeder->seed();

        // And I've picked one monitored job
        /** @var MonitoredJob $randomJob */
        $randomJob = $this->getSeededData(0, 'random-job')($this->seeder);

        // And I've got the concrete repository from the IoC for "MonitoredJobRepositoryInterface"
        $repository = $this->getConcreteRepository();

        // And I have a new desired uuid token to replace another one
        $newToken = Uuid::uuid4()->toString();
        // And I have a new desired `created_at` date to replace another one
        $yesterday = Carbon::now()->subDay()->format('Y-m-d H:i:s');

        // When I call the method `update` with certain parameters that I know it will not update them
        $repository->update($randomJob->token, ['token' => $newToken, 'created_at' => $yesterday]);

        // Then I call the method `get` with certain parameters that I know it will not return a model
        $job = $repository->get(['token' => $newToken]);
        // And I should see that the value retrieved from method `get` is null
        self::assertNull($job);

        // Also, when I call the method `get` with certain parameters that I know it will return a model
        $job = $repository->get(['token' => $randomJob->token]);
        // Then I should see that my expected token is the same retrieved from method `get`
        self::assertSame($randomJob->token, $job->token);
        // And I should see that the attribute `created_at` was not updated as expected
        self::assertNotSame($randomJob->created_at->format('Y-m-d H:i:s'), $yesterday);


        // Also, given I have a known progress to replace another one
        $newProgress = (float)$this->faker->unique()->numberBetween(20, 80);
        // And I have a new desired `queue_job_id` to replace another one
        $queueableJobId = $this->faker->unique()->numberBetween(2500, 25000);

        // When I call the method `update` with certain parameters that I know it will update them
        $repository->update($randomJob->token, ['progress' => $newProgress, 'queue_job_id' => $queueableJobId]);

        // Then I call the method `get` with certain parameters that I know it will return a model
        $job = $repository->get(['token' => $randomJob->token]);
        // And I should see that my expected progress is the same retrieved from method `get`
        self::assertSame($newProgress, $job->progress);
        // And I should see that the attribute `queue_job_id` was updated as expected
        self::assertNotSame($job->queue_job_id, $queueableJobId);
    }

    /**
     * @covers ::updateProgress
     * @group DMS
     * @group DMS_MONITORED_JOBS
     *
     * @throws BindingResolutionException
     */
    public function testUpdateProgressIsWorkingProperly(): void
    {
        // Given I have few monitored jobs
        $this->seeder->seed();

        // And I've picked one monitored job
        /** @var MonitoredJob $randomJob */
        $randomJob = $this->getSeededData(0, 'random-job')($this->seeder);

        // And I've got the concrete repository from the IoC for "MonitoredJobRepositoryInterface"
        $repository = $this->getConcreteRepository();

        // And I have a known progress to replace another one
        $newProgress = (float)$this->faker->unique()->numberBetween(20, 80);

        // When I call the method `updateProgress` with certain progress
        $repository->updateProgress($randomJob->token, $newProgress);
        // Then I call the method `findByToken` using my known token
        $job = $repository->findByToken($randomJob->token);
        // And I should see that my expected progress is the same retrieved from method `findByToken`
        self::assertSame($newProgress, $job->progress);
        // And I should see that the new status of that job is `processing`
        self::assertSame(MonitoredJob::STATUS_PROCESSING, $job->status);
    }

    /**
     * @covers ::updateProgress
     * @group DMS
     * @group DMS_MONITORED_JOBS
     *
     * Test that SUT is updating the job status to `completed` when the progress is greater or equal than 100
     *
     * @throws BindingResolutionException
     */
    public function testUpdateProgressForGreaterOrEqualThan100(): void
    {
        // Given I have few monitored jobs
        $this->seeder->seed();

        // And I've picked one monitored job
        /** @var MonitoredJob $randomJob */
        $randomJob = $this->getSeededData(0, 'random-job')($this->seeder);

        // And I've got the concrete repository from the IoC for "MonitoredJobRepositoryInterface"
        $repository = $this->getConcreteRepository();

        // And I have a known progress to replace another one
        $newProgress = 100.0;

        // When I call the method `updateProgress` with certain progress
        $repository->updateProgress($randomJob->token, $newProgress);
        // Then I call the method `findByToken` using my known token
        $job = $repository->findByToken($randomJob->token);
        // And I should see that my expected progress is the same retrieved from method `findByToken`
        self::assertSame($newProgress, $job->progress);
        // And I should see that the new status of that job is `completed`
        self::assertSame(MonitoredJob::STATUS_COMPLETED, $job->status);
        // And I should see that the `finished_at` is not null
        self::assertNotEmpty($job->finished_at->format('Y-m-d H:i:s'));
    }

    /**
     * @covers ::setFailed
     * @group DMS
     * @group DMS_MONITORED_JOBS
     *
     * @throws BindingResolutionException
     */
    public function testSetFailedIsWorkingProperly(): void
    {
        // Given I have few monitored jobs
        $this->seeder->seed();

        // And I've picked one monitored job
        /** @var MonitoredJob $randomJob */
        $randomJob = $this->getSeededData(0, 'random-job')($this->seeder);

        // And I've got the concrete repository from the IoC for "MonitoredJobRepositoryInterface"
        $repository = $this->getConcreteRepository();

        // And I have a payload result
        $result = MonitoredJobResult::from(['message' => 'Everything goes wrong']);

        // When I call the method `setFailed` with certain progress
        $repository->setFailed($randomJob->token, $result);
        // Then I call the method `findByToken` using my known token
        $job = $repository->findByToken($randomJob->token);
        // And I should see that the new status of that job is `failed`
        self::assertSame(MonitoredJob::STATUS_FAILED, $job->status);
        // And I should see that the `finished_at` is not null
        self::assertNotEmpty($job->finished_at->format('Y-m-d H:i:s'));
        // And I should see that the `message` from the result payload is the same retrieved from `findByToken`
        self::assertSame($result->message, $job->result['message']);
    }

    /**
     * @covers ::updateResult
     * @group DMS
     * @group DMS_MONITORED_JOBS
     *
     * @throws BindingResolutionException
     */
    public function testUpdateResultIsWorkingProperly(): void
    {
        // Given I have few monitored jobs
        $this->seeder->seed();

        // And I've picked one monitored job
        /** @var MonitoredJob $randomJob */
        $randomJob = $this->getSeededData(0, 'random-job')($this->seeder);

        // And I've got the concrete repository from the IoC for "MonitoredJobRepositoryInterface"
        $repository = $this->getConcreteRepository();

        // And I have a payload result
        $result = MonitoredJobResult::from(['message' => 'Everything goes right']);

        // When I call the method `updateResult` with certain progress
        $repository->updateResult($randomJob->token, $result);
        // Then I call the method `findByToken` using my known token
        $job = $repository->findByToken($randomJob->token);
        // I should see that the `message` from the result payload is the same retrieved from `findByToken`
        self::assertSame($result->message, $job->result['message']);
    }

    /**
     * Test that SUT throws an specific exception when there is not a monitored job according to the provided token
     *
     * @covers ::setCompleted
     * @covers ::updateProgress
     * @covers ::updateResult
     * @covers ::setFailed
     *
     * @group DMS
     * @group DMS_MONITORED_JOBS
     *
     * @dataProvider availableMethodsProvider
     *
     * @param callable $methodToTest
     * @throws BindingResolutionException
     * @throws Exception when Uuid::uuid4()->toString() could not generate a uuid
     */
    public function testMethodThrowsAnException(callable $methodToTest): void
    {
        // Given I have few monitored jobs
        $this->seeder->seed();

        // And I've got the concrete repository from the IoC for "MonitoredJobRepositoryInterface"
        $repository = $this->getConcreteRepository();

        // And I have a non existing token
        $nonExistingToken = Uuid::uuid4()->toString();

        // Then I expect to see an "ModelNotFoundException" to be thrown
        $this->expectException(ModelNotFoundException::class);
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage(sprintf('No query results for model [%s] %s', MonitoredJob::class, $nonExistingToken));

        // When I call the method a method with certain progress that I know it will throw an exception
        $methodToTest($repository, $nonExistingToken);
    }

    /**
     * Test that SUT is creating the monitored job as expected with the status `processing`,
     * also it make sure that since there is a previously created job, when it is call `isBusyByDealer` it returns true,
     * and when it is call `isBusyByJobName` it returns true as well.
     *
     * @covers ::create
     * @covers ::isBusyByDealer
     * @covers ::isBusyByJobName
     *
     * @group DMS
     * @group DMS_MONITORED_JOBS
     *
     * @throws BindingResolutionException
     * @throws Exception when Uuid::uuid4()->toString() could not generate a uuid
     */
    public function testCreatedJobAndCheckIfIsBusyByDealerAndJobName(): void
    {
        // Given I have few dealers
        $this->seeder->seedDealers();

        // And I've got the concrete repository from the IoC for "MonitoredJobRepositoryInterface"
        $repository = $this->getConcreteRepository();

        // And I have a non existing token
        $token = Uuid::uuid4()->toString();

        // And I've picked one dealer
        $dealerId = $this->seeder->dealers[0]->dealer_id;

        // And I have a job name
        $jobName = MonitoredJob::QUEUE_JOB_NAME;

        // Then I will create a monitored job wit status `processing`
        $createdJob = $repository->create([
            'token' => $token,
            'dealer_id' => $dealerId,
            'name' => $jobName,
            'concurrency_level' => MonitoredJob::LEVEL_DEFAULT,
            'queue' => MonitoredJob::QUEUE_NAME,
            'status' => MonitoredJob::STATUS_PROCESSING
        ]);

        // Then I call the method `findByToken` using my known token
        $foundJob = $repository->findByToken($token);

        // And I should see that the created job is the same as the found job
        self::assertSame($createdJob->name, $foundJob->name);

        // Also, when I call the method `isBusyByDealer` using my known token
        $result = $repository->isBusyByDealer($dealerId);
        // Then I should see that it is true
        self::assertTrue($result);

        // Also, when I call the method `isBusyByJobName` using my known token
        $result = $repository->isBusyByJobName($jobName);
        // Then I should see that it is true
        self::assertTrue($result);
    }

    /**
     * Examples of methods which will thrown an exception when there is not a monitored job according to the provided token
     *
     * @return array<string, callable>
     * @throws Exception when Uuid::uuid4 cannot generate a uuid
     */
    public function availableMethodsProvider(): array
    {
        $validPayload = MonitoredJobResult::from(['message' => 'Everything goes right']);

        return [               // callable $methodToTest
            'setCompleted' => [static function (MonitoredJobRepositoryInterface $repository, string $token): bool {
                return $repository->setCompleted($token);
            }],
            'setFailed' => [static function (MonitoredJobRepositoryInterface $repository, string $token) use ($validPayload): bool {
                return $repository->setFailed($token, $validPayload);
            }],
            'updateProgress' => [static function (MonitoredJobRepositoryInterface $repository, string $token): bool {
                return $repository->updateProgress($token, 45);
            }],
            'updateResult' => [static function (MonitoredJobRepositoryInterface $repository, string $token) use ($validPayload): bool {
                return $repository->updateResult($token, $validPayload);
            }]
        ];
    }

    /**
     * Examples of parameters, expected total and last page numbers, and first monitored job name.
     *
     * @return array<string, array>
     */
    public function queryParametersAndSummariesProvider(): array
    {
        return [                                     // array $parameters, int $expectedTotal, int $expectedLastPage
            'No parameters'                          => [[], 12, 1],
            'By dummy dealer page by 2'              => [['dealer_id' => $this->getSeededData(0,'id'), 'per_page' => 2], 8, 4],
            'By other dummy dealer page by 2'        => [['dealer_id' => $this->getSeededData(1,'id'), 'per_page' => 1], 4, 4],
            'By dummy dealer paged at 100 (default)' => [['dealer_id' => $this->getSeededData(0,'id')], 8, 1]
        ];
    }

    /**
     * @return MonitoredJobRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): MonitoredJobRepositoryInterface
    {
        return $this->app->make(MonitoredJobRepositoryInterface::class);
    }
}
