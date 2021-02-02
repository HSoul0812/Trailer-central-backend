<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Contollers\Jobs;

use App\Http\Controllers\v1\Jobs\MonitoredJobsController;
use App\Http\Requests\Jobs\GetMonitoredJobsRequest;
use App\Models\Common\MonitoredJob;
use Dingo\Api\Exception\ResourceException;
use Exception;
use Faker\Factory as Faker;
use Illuminate\Pagination\LengthAwarePaginator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\database\seeds\Common\MonitoredJobSeeder;
use Tests\TestCase;

/**
 * @covers \App\Http\Controllers\v1\Jobs\MonitoredJobsController
 */
class MonitoredJobsControllerTest extends TestCase
{
    /**
     * @var MonitoredJobSeeder
     */
    private $seeder;

    /**
     * @covers ::index
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
     * @dataProvider validQueryParameterProvider
     *
     * @param array $params
     * @param callable $expectedTotal callable(): int
     * @param int $expectedLastPage
     * @param callable $expectedJobs callable(): \Illuminate\Support\Collection<MonitoredJob>
     *
     * @covers ::index
     */
    public function testIndexListJobsPerDealer(array $params,
                                          callable $expectedTotal,
                                          int $expectedLastPage,
                                          callable $expectedJobs): void
    {
        /** @var LengthAwarePaginator $paginator */

        // Given I have few monitored jobs which belongs to a specific dealer
        $this->seeder->seed();
        // And I'm using the a "MonitoredJobsController" controller
        $controller = app(MonitoredJobsController::class);
        // And I have a well formed "GetMonitoredJobsRequest" request
        $request = new GetMonitoredJobsRequest($this->seeder->extractValues($params));

        // When I call the index action using the well formed request
        $response = $controller->index($request);
        $paginator = $response->original;

        // Then I should see that response status is 200
        self::assertSame(200, $response->status());
        // And I should see that the expected total of monitored jobs is the same as monitored jobs retrieved
        self::assertSame($expectedTotal($this->seeder), $paginator->total());
    }

    /**
     * @throws Exception when Uuid::uuid4 cannot generate a uuid
     *
     * @covers ::status
     */
    public function testMonitoredJobDoesNotExists(): void
    {
        // Given I have a token of non-existent monitored job
        $token = Uuid::uuid4()->toString();
        // And I'm using the a "MonitoredJobsController" controller
        $controller = app(MonitoredJobsController::class);

        // Then I expect to see an "HttpException" to be thrown
        $this->expectException(HttpException::class);
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage('The job was not found');

        // When I call the status action using the token of non-existent monitored job
        $controller->status($token);
    }

    /**
     * @throws Exception when Uuid::uuid4 cannot generate a uuid
     *
     * @covers ::status
     */
    public function testListJobsPerDealer(): void
    {
        /** @var MonitoredJob  $job */
        /** @var array<string, float> $response */

        $faker = Faker::create();

        // Given I have few monitored jobs
        $this->seeder->seed();

        // And I'm a dealer with a specific id
        $dealerId = $this->seeder->dealers[0]->getKey();

        // And I've randomly picked one of my monitored jobs
        $job = $this->seeder->jobs[$dealerId]->get($faker->unique()->numberBetween(0, 7));

        // And I'm using the a "MonitoredJobsController" controller
        $controller = app(MonitoredJobsController::class);

        // When I call the status action using the token of my picked monitored job
        $response = $controller->status($job->token);

        // Then I should see that response status is 200
        self::assertSame(200, $response->status());
        // And I should see that the expected response has a specific structure
        self::assertSame(['message' => 'Still processing', 'progress' => $job->progress], $response->original);
    }

    /**
     * Examples of valid query parameter with their respective expected number of records and pages
     *
     * @return array[]
     */
    public function validQueryParameterProvider(): array
    {
        /**
         * Since the data provider is called before the setup, it is necessary to use a lambda to retrieve data
         *
         * @param int $index
         * @param string $keyName
         * @return callable
         */
        $dealerLambda = static function (int $index,string $keyName): callable {
            /**
             * @param MonitoredJobSeeder $seeder
             * @return mixed
             */
            return static function (MonitoredJobSeeder $seeder) use ($index, $keyName) {
                $dealerId = $seeder->dealers[$index]->getKey();

                switch ($keyName) {
                    case 'id':
                        return $dealerId;
                    case 'total':
                        return count($seeder->jobs[$dealerId]);
                    case 'jobs':
                        return $seeder->jobs[$dealerId];
                }

                return null;
            };
        };

        // array $parameters, callable:int $expectedTotal, int $expectedLastPage, callable:\Illuminate\Support\Collection<MonitoredJob> $expectedJobs
        return [
            'By dummy dealer paged by 2'           => [['dealer_id' => $dealerLambda(0,'id'), 'per_page' => 2], $dealerLambda(0,'total'), 4, $dealerLambda(0, 'jobs')],
            'By another dummy dealer paged by 100' => [['dealer_id' => $dealerLambda(1, 'id')], $dealerLambda(1,'total'), 1, $dealerLambda(1, 'jobs')]
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new MonitoredJobSeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }
}
