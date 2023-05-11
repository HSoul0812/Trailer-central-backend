<?php

namespace Tests\Integration\Repositories\Showroom;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Showroom\ShowroomGenericMap;
use App\Repositories\Showroom\ShowroomGenericMapRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Tests\database\seeds\Showroom\ShowroomSeeder;
use Tests\TestCase;

/**
 * Test for App\Repositories\Showroom\ShowroomGenericMapRepository
 *
 * Class ShowroomGenericMapRepositoryTest
 * @package Tests\Integration\Repositories\Showroom
 *
 * @coversDefaultClass \App\Repositories\Showroom\ShowroomGenericMapRepository
 */
class ShowroomGenericMapRepositoryTest extends TestCase
{
    /**
     * @covers ::getAll
     *
     * @group INTEGRATIONS
     * @group INTEGRATIONS_SHOWROOM
     */
    public function testGetAll()
    {
        $showroomGenericMapsCount = 3;

        $seeder = new ShowroomSeeder(['showroomGenericMapsCount' => $showroomGenericMapsCount]);
        $seeder->seed();

        /** @var ShowroomGenericMapRepositoryInterface $showroomGenericMapRepository */
        $showroomGenericMapRepository = app()->make(ShowroomGenericMapRepositoryInterface::class);

        /** @var Collection $showroomGenericMaps */
        $showroomGenericMaps = $showroomGenericMapRepository->getAll(['external_mfg_key' => $seeder->showroomGenericMaps[0]->external_mfg_key]);

        $this->assertInstanceOf(Collection::class, $showroomGenericMaps);
        $this->assertSame(1, $showroomGenericMaps->count());

        $this->assertInstanceOf(ShowroomGenericMap::class, $showroomGenericMaps->first());
        $this->assertSame($seeder->getShowroomId(), $showroomGenericMaps->first()->showroom_id);
        $this->assertSame($seeder->showroomGenericMaps[0]->external_mfg_key, $showroomGenericMaps->first()->external_mfg_key);

        $seeder->cleanUp();
    }

    /**
     * @covers ::getAll
     *
     * @group INTEGRATIONS
     * @group INTEGRATIONS_SHOWROOM
     */
    public function testGetAllShowroomIdNotExist()
    {
        $showroomGenericMapsCount = 1;
        $wrongExternalMfgKey = PHP_INT_MAX;

        $seeder = new ShowroomSeeder(['showroomGenericMapsCount' => $showroomGenericMapsCount]);
        $seeder->seed();

        /** @var ShowroomGenericMapRepositoryInterface $showroomGenericMapRepository */
        $showroomGenericMapRepository = app()->make(ShowroomGenericMapRepositoryInterface::class);

        /** @var Collection $showroomGenericMapRepository */
        $showroomGenericMaps = $showroomGenericMapRepository->getAll(['external_mfg_key' => $wrongExternalMfgKey]);

        $this->assertInstanceOf(Collection::class, $showroomGenericMaps);
        $this->assertSame(0, $showroomGenericMaps->count());

        $seeder->cleanUp();
    }

    /**
     * @covers ::getAll
     *
     * @group INTEGRATIONS
     * @group INTEGRATIONS_SHOWROOM
     */
    public function testGetAllWithoutRequiredParams()
    {
        $this->expectException(RepositoryInvalidArgumentException::class);

        $wrongParams = ['wrong_param' => 'wrong_value'];

        /** @var ShowroomGenericMapRepositoryInterface $showroomGenericMapRepository */
        $showroomGenericMapRepository = app()->make(ShowroomGenericMapRepositoryInterface::class);

        $showroomGenericMapRepository->getAll($wrongParams);
    }
}
