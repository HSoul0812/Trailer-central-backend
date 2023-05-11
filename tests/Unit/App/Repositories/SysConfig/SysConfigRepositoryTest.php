<?php

namespace Tests\Unit\App\Repositories\SysConfig;

use App\Repositories\SysConfig\SysConfigRepository;
use App\Repositories\SysConfig\SysConfigRepositoryInterface;
use Database\Seeders\SysConfig\BannerSeeder;
use Database\Seeders\SysConfig\FilterSeeder;
use Tests\Common\TestCase;

class SysConfigRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->seed(FilterSeeder::class);
        $this->seed(BannerSeeder::class);
    }

    public function testGetAllWithParams()
    {
        $repository = $this->getConcreteRepository();
        $filters = $repository->getAll(['key' => 'filter/size/']);
        self::assertEquals($filters->count(), 6);

        $banners = $repository->getAll(['key' => 'banner/']);
        self::assertEquals($banners->count(), 38);
    }

    public function testGetAllWithoutParams()
    {
        $repository = $this->getConcreteRepository();
        $configs = $repository->getAll([]);
        self::assertEquals($configs->count(), 72);
    }

    private function getConcreteRepository(): SysConfigRepository
    {
        return app()->make(SysConfigRepositoryInterface::class);
    }
}
