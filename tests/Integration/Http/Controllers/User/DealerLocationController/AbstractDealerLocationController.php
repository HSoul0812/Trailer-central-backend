<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\User\DealerLocationController;

use Faker\Factory as Faker;
use Faker\Generator;
use Tests\database\seeds\User\DealerLocationSeeder;
use Tests\TestCase;

class AbstractDealerLocationController extends TestCase
{
    /** @var DealerLocationSeeder */
    protected $seeder;

    /**
     * @var Generator
     */
    protected $faker;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name , $data, $dataName);

        $this->faker = $this->faker ?? Faker::create();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = $this->seeder ?? new DealerLocationSeeder();
        $this->seeder->seed();
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
        /**
         * @param DealerLocationSeeder $seeder
         * @return mixed
         */
        return static function (DealerLocationSeeder $seeder) use ($dealerIndex, $keyName) {
            $dealerId = $seeder->dealers[$dealerIndex]->getKey();

            switch ($keyName) {
                case 'dealerId':
                    return $dealerId;
                case 'totalOfLocations':
                    return $seeder->locations[$dealerId]->count();
                case 'locations':
                    return $seeder->locations[$dealerId];
                case 'randomLocation':
                    return $seeder->locations[$dealerId]->random();
                case 'firstLocation':
                    return $seeder->locations[$dealerId]->first();
                case 'firstLocationId':
                    return $seeder->locations[$dealerId]->first()->dealer_location_id;
                case 'firstLocationName':
                    return $seeder->locations[$dealerId]->first()->name;
            }

            return null;
        };
    }
}
