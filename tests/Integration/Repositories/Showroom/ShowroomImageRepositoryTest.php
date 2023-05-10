<?php

namespace Tests\Integration\Repositories\Showroom;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Showroom\ShowroomImage;
use App\Repositories\Showroom\ShowroomImageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Tests\database\seeds\Showroom\ShowroomSeeder;
use Tests\TestCase;

/**
 * Test for App\Repositories\Showroom\ShowroomImageRepository
 *
 * Class ShowroomImageRepositoryTest
 * @package Tests\Integration\Repositories\Showroom
 *
 * @coversDefaultClass \App\Repositories\Showroom\ShowroomImageRepository
 */
class ShowroomImageRepositoryTest extends TestCase
{
    /**
     * @covers ::getAll
     *
     * @group INTEGRATIONS
     * @group INTEGRATIONS_SHOWROOM
     */
    public function testGetAll()
    {
        $showroomImagesCount = 3;

        $seeder = new ShowroomSeeder(['showroomImagesCount' => $showroomImagesCount]);
        $seeder->seed();

        /** @var ShowroomImageRepositoryInterface $showroomImageRepository */
        $showroomImageRepository = app()->make(ShowroomImageRepositoryInterface::class);

        /** @var Collection $showroomImages */
        $showroomImages = $showroomImageRepository->getAll(['showroom_id' => $seeder->getShowroomId()]);

        $this->assertInstanceOf(Collection::class, $showroomImages);
        $this->assertSame($showroomImagesCount, $showroomImages->count());

        foreach ($showroomImages as $showroomImage) {
            $this->assertInstanceOf(ShowroomImage::class, $showroomImage);
            $this->assertSame($seeder->getShowroomId(), $showroomImage->showroom_id);
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
        $showroomImagesCount = 1;
        $wrongShowroomId = PHP_INT_MAX;

        $seeder = new ShowroomSeeder(['showroomImagesCount' => $showroomImagesCount]);
        $seeder->seed();

        /** @var ShowroomImageRepositoryInterface $showroomImageRepository */
        $showroomImageRepository = app()->make(ShowroomImageRepositoryInterface::class);

        /** @var Collection $showroomImages */
        $showroomImages = $showroomImageRepository->getAll(['showroom_id' => $wrongShowroomId]);

        $this->assertInstanceOf(Collection::class, $showroomImages);
        $this->assertSame(0, $showroomImages->count());

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

        /** @var ShowroomImageRepositoryInterface $showroomImageRepository */
        $showroomImageRepository = app()->make(ShowroomImageRepositoryInterface::class);

        $showroomImageRepository->getAll($wrongParams);
    }
}
