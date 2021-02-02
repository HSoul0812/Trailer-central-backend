<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Contollers\Jobs\MonitoredJobsController;

use App\Http\Controllers\v1\Jobs\MonitoredJobsController;
use App\Models\Common\MonitoredJob;
use Faker\Factory as Faker;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\database\seeds\Common\MonitoredJobSeeder;
use Tests\TestCase;
use Exception;

/**
 * @covers \App\Http\Controllers\v1\Jobs\MonitoredJobsController::status
 */
class StatusTest extends TestCase
{
    /**
     * @var MonitoredJobSeeder
     */
    private $seeder;

    /**
     * @throws Exception when Uuid::uuid4 cannot generate a uuid
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

        // When I call the index action using the token of my picked monitored job
        $response = $controller->status($job->token);

        // Then I should see that response status is 200
        self::assertSame(200, $response->status());
        // And I should see that the expected response has a specific structure
        self::assertSame(['message' => 'Still processing', 'progress' => $job->progress], $response->original);
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
