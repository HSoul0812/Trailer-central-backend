<?php

namespace Tests\Integration\Repositories\Showroom;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Showroom\ShowroomFile;
use App\Repositories\Showroom\ShowroomFileRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Tests\database\seeds\Showroom\ShowroomSeeder;
use Tests\TestCase;

/**
 * Test for App\Repositories\Showroom\ShowroomFileRepository
 *
 * Class ShowroomFileRepositoryTest
 * @package Tests\Integration\Repositories\Showroom
 *
 * @coversDefaultClass \App\Repositories\Showroom\ShowroomFileRepository
 */
class ShowroomFileRepositoryTest extends TestCase
{
    /**
     * @covers ::getAll
     *
     * @group INTEGRATIONS
     * @group INTEGRATIONS_SHOWROOM
     */
    public function testGetAll()
    {
        $showroomFilesCount = 0;

        $seeder = new ShowroomSeeder(['showroomFilesCount' => $showroomFilesCount]);
        $seeder->seed();

        /** @var ShowroomFileRepositoryInterface $showroomFileRepository */
        $showroomFileRepository = app()->make(ShowroomFileRepositoryInterface::class);

        /** @var Collection $showroomFiles */
        $showroomFiles = $showroomFileRepository->getAll(['showroom_id' => $seeder->getShowroomId()]);

        $this->assertInstanceOf(Collection::class, $showroomFiles);
        $this->assertSame($showroomFilesCount, $showroomFiles->count());

        foreach ($showroomFiles as $showroomFile) {
            $this->assertInstanceOf(ShowroomFile::class, $showroomFile);
            $this->assertSame($seeder->getShowroomId(), $showroomFile->showroom_id);
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
        $showroomFilesCount = 1;
        $wrongShowroomId = PHP_INT_MAX;

        $seeder = new ShowroomSeeder(['showroomFilesCount' => $showroomFilesCount]);
        $seeder->seed();

        /** @var ShowroomFileRepositoryInterface $showroomFileRepository */
        $showroomFileRepository = app()->make(ShowroomFileRepositoryInterface::class);

        /** @var Collection $showroomFiles */
        $showroomFiles = $showroomFileRepository->getAll(['showroom_id' => $wrongShowroomId]);

        $this->assertInstanceOf(Collection::class, $showroomFiles);
        $this->assertSame(0, $showroomFiles->count());

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

        /** @var ShowroomFileRepositoryInterface $showroomFileRepository */
        $showroomFileRepository = app()->make(ShowroomFileRepositoryInterface::class);

        $showroomFileRepository->getAll($wrongParams);
    }
}
