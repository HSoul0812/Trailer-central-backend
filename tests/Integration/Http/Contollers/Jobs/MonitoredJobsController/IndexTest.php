<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Contollers\Jobs\MonitoredJobsController;

use App\Http\Controllers\v1\Jobs\MonitoredJobsController;
use App\Http\Requests\Jobs\GetMonitoredJobsRequest;
use App\Models\Common\MonitoredJob;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\database\seeds\Common\MonitoredJobSeeder;
use Tests\TestCase;

/**
 * @covers \App\Http\Controllers\v1\Jobs\MonitoredJobsController::index
 */
class IndexTest extends TestCase
{
    /**
     * @var MonitoredJobSeeder
     */
    private $seeder;

    public function testInvalidParameter(): void
    {
        // Given I'm using the controller "MonitoredJobsController"
        $controller = app(MonitoredJobsController::class);
        // And I have a poor formed "GetMonitoredJobsRequest"
        $request = new GetMonitoredJobsRequest([]);

        // Then I expect to see an "ResourceException" to be thrown
        $this->expectException(ResourceException::class);
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage('Validation Failed');

        try {
            // When I call the index action
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
     */
    public function testListJobsPerDealer(array $params,
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

        // When I call the index action
        $response = $controller->index($request);
        $paginator = $response->original;

        // Then I should see that response status is 200
        self::assertSame(200, $response->status());
        // And I should see that the expected total of monitored jobs is the same as monitored jobs retrieved
        self::assertSame($expectedTotal($this->seeder), $paginator->total());

        $expectedJobs($this->seeder)->each(function (MonitoredJob $job) {
            $this->assertDatabaseHas(
                MonitoredJob::getTableName(),
                ['token' => $job->token, 'name' => $job->name, 'status' => $job->status]
            );
        });
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

        return [                                   // array $parameters, callable:int $expectedTotal, int $expectedLastPage, callable:\Illuminate\Support\Collection<MonitoredJob> $expectedJobs
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
