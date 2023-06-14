<?php

namespace Tests\Integration\Repositories\Location;

use App\Models\User\Location\Geolocation;
use App\Repositories\User\GeoLocationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_GEOLOCATION
 */
class GeolocationRepositoryTest extends TestCase
{
    /** @var Collection */
    private $searchData;

    const SEARCH_DATA_COUNT = 10;

    const DATA_PREFIX = 'test_';

    public function setUp(): void
    {
        parent::setUp();
        $this->createSearchData();
    }

    private function createSearchData()
    {
        $this->searchData = collect([]);
        for ($i = 0; $i < self::SEARCH_DATA_COUNT; $i++) {
            $this->searchData->push(factory(Geolocation::class)->create([
                'city' => self::DATA_PREFIX . Str::random(3),
                'zip' => self::DATA_PREFIX . Str::random(2)
            ]));
        }
    }

    public function testItCanSearchGivenParameters()
    {
        /** @var GeoLocationRepositoryInterface $repository */
        $repository = $this->app->make(GeoLocationRepositoryInterface::class);
        $locations = $repository->search(['zip' => self::DATA_PREFIX]);
        $this->assertSame(self::SEARCH_DATA_COUNT, $locations->count());

        $locations->each(function ($location) {
            $this->assertNotNull($this->searchData->firstWhere('zip', $location->zip));
        });

        $locations = $repository->search(['city' => self::DATA_PREFIX]);
        $this->assertSame(self::SEARCH_DATA_COUNT, $locations->count());

        $locations->each(function ($location) {
            $this->assertNotNull($this->searchData->firstWhere('city', $location->city));
        });
    }

    public function cleanupSearchData()
    {
        $this->searchData->each(function ($location) {
            $location->delete();
        });
    }

    public function tearDown(): void
    {
        $this->cleanupSearchData();

        parent::tearDown();
    }
}
