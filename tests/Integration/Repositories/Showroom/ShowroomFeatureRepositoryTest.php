<?php

namespace Tests\Integration\Repositories\Showroom;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Showroom\ShowroomFeature;
use App\Repositories\Showroom\ShowroomFeatureRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Tests\database\seeds\Showroom\ShowroomSeeder;
use Tests\TestCase;

/**
 * Test for App\Repositories\Showroom\ShowroomFeatureRepository
 *
 * Class ShowroomFeatureRepositoryTest
 * @package Tests\Integration\Repositories\Showroom
 *
 * @coversDefaultClass \App\Repositories\Showroom\ShowroomFeatureRepository
 */
class ShowroomFeatureRepositoryTest extends TestCase
{
    /**
     * @covers ::getAll
     *
     * @group INTEGRATIONS
     * @group INTEGRATIONS_SHOWROOM
     */
    public function testGetAll()
    {
        $showroomFeaturesCount = 3;

        $seeder = new ShowroomSeeder(['showroomFeaturesCount' => $showroomFeaturesCount]);
        $seeder->seed();

        /** @var ShowroomFeatureRepositoryInterface $showroomFeatureRepository */
        $showroomFeatureRepository = app()->make(ShowroomFeatureRepositoryInterface::class);

        /** @var Collection $showroomFeatures */
        $showroomFeatures = $showroomFeatureRepository->getAll(['showroom_id' => $seeder->getShowroomId()]);

        $this->assertInstanceOf(Collection::class, $showroomFeatures);
        $this->assertSame($showroomFeaturesCount, $showroomFeatures->count());

        foreach ($showroomFeatures as $showroomFeature) {
            $this->assertInstanceOf(ShowroomFeature::class, $showroomFeature);
            $this->assertSame($seeder->getShowroomId(), $showroomFeature->showroom_id);
        }

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
        $showroomFeaturesCount = 1;
        $wrongShowroomId = PHP_INT_MAX;

        $seeder = new ShowroomSeeder(['showroomFeaturesCount' => $showroomFeaturesCount]);
        $seeder->seed();

        /** @var ShowroomFeatureRepositoryInterface $showroomFeatureRepository */
        $showroomFeatureRepository = app()->make(ShowroomFeatureRepositoryInterface::class);

        /** @var Collection $showroomFeatures */
        $showroomFeatures = $showroomFeatureRepository->getAll(['showroom_id' => $wrongShowroomId]);

        $this->assertInstanceOf(Collection::class, $showroomFeatures);
        $this->assertSame(0, $showroomFeatures->count());

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

        /** @var ShowroomFeatureRepositoryInterface $showroomFeatureRepository */
        $showroomFeatureRepository = app()->make(ShowroomFeatureRepositoryInterface::class);

        $showroomFeatureRepository->getAll($wrongParams);
    }
}
