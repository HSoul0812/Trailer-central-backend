<?php

namespace Tests\Unit\App\Repositories\SysConfig;

use App\Repositories\SysConfig\SysConfigRepository;
use App\Repositories\SysConfig\SysConfigRepositoryInterface;
use Database\Seeders\SysConfig\FilterSeeder;
use Tests\Common\TestCase;

class SysConfigRepositoryTest extends TestCase
{
    public function testGetAllWithParams() {
        $repository = $this->getConcreteRepository();
        $filters = $repository->getAll(['key' => 'filter/size/']);
        self::assertEquals($filters->count(), 6);
    }

    public function testGetAllWithoutParams() {
        $repository = $this->getConcreteRepository();
        $filters = $repository->getAll([]);
        self::assertEquals($filters->count(),34);
    }
    public function setUp(): void
    {
        parent::setUp();
        $this->seed(FilterSeeder::class);
    }

    private function getConcreteRepository(): SysConfigRepository {
        return app()->make(SysConfigRepositoryInterface::class);
    }
}
