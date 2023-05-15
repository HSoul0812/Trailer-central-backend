<?php

namespace Tests\Unit\App\Repositories\SysConfig;

use App\Repositories\SysConfig\SysConfigRepository;
use App\Repositories\SysConfig\SysConfigRepositoryInterface;
use Database\Seeders\SysConfig\BannerSeeder;
use Database\Seeders\SysConfig\FilterSeeder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\Common\TestCase;

class SysConfigRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FilterSeeder::class);
        $this->seed(BannerSeeder::class);
    }

    /**
     * @throws BindingResolutionException
     */
    public function testGetAllWithParams()
    {
        $repository = $this->getConcreteRepository();

        $this->assertNotEmpty(
            actual: $repository->getAll(['key' => 'filter/size/']),
        );

        $this->assertNotEmpty(
            actual: $repository->getAll(['key' => 'banner/']),
        );
    }

    /**
     * @throws BindingResolutionException
     */
    public function testGetAllWithoutParams()
    {
        $repository = $this->getConcreteRepository();

        $this->assertNotEmpty(
            actual: $repository->getAll([]),
        );
    }

    /**
     * @throws BindingResolutionException
     */
    private function getConcreteRepository(): SysConfigRepository
    {
        return app()->make(SysConfigRepositoryInterface::class);
    }
}
