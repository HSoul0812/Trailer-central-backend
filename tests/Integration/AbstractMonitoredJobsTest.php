<?php

declare(strict_types=1);

namespace Tests\Integration;

use Faker\Factory as Faker;
use Faker\Generator;
use Tests\database\seeds\Common\MonitoredJobSeeder;
use Tests\TestCase;

abstract class AbstractMonitoredJobsTest extends TestCase
{
    /**
     * @var MonitoredJobSeeder
     */
    protected $seeder;

    /**
     * @var Generator
     */
    protected $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = $this->faker ?? Faker::create();

        $this->seeder = $this->seeder ?? new MonitoredJobSeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @param int $dealerIndex the array index of a dealer
     * @param string $keyName the key name of the needed value
     * @return callable
     */
    protected function getSeededData(int $dealerIndex, string $keyName): callable
    {
        $this->faker = Faker::create();

        /**
         * @param MonitoredJobSeeder $seeder
         * @return mixed
         */
        return function (MonitoredJobSeeder $seeder) use ($dealerIndex, $keyName) {
            $dealerId = $seeder->dealers[$dealerIndex]->getKey();

            switch ($keyName) {
                case 'id':
                    return $dealerId;
                case 'total':
                    return count($seeder->jobs[$dealerId]);
                case 'jobs':
                    return $seeder->jobs[$dealerId];
                case 'random-token':
                    $jobs = $seeder->jobs[$dealerId];
                    $max = count($jobs);

                    return $jobs[$this->faker->numberBetween(0, $max - 1)]->token;
                case 'random-job':
                    $jobs = $seeder->jobs[$dealerId];
                    $max = count($jobs);

                    return $jobs[$this->faker->numberBetween(0, $max - 1)];
                case 'first-job-name':
                    $jobs = $seeder->jobs[$dealerId];

                    return $jobs[0]->name;
            }

            return null;
        };
    }
}
