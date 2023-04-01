<?php

namespace Tests\Unit\Services\Inventory\Packages;

use App\Exceptions\Inventory\Packages\PackageException;
use App\Models\Inventory\Packages\Package;
use App\Repositories\Inventory\Packages\PackageRepositoryInterface;
use App\Services\Inventory\Packages\PackageService;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_INVENTORY
 *
 * Class PackageServiceTest
 * @package Tests\Unit\Services\Inventory\Packages
 *
 * @coversDefaultClass \App\Services\Inventory\Packages\PackageService
 */
class PackageServiceTest extends TestCase
{
    /**
     * @var LegacyMockInterface|PackageRepositoryInterface
     */
    private $packageRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->packageRepositoryMock = Mockery::mock(PackageRepositoryInterface::class);
        $this->app->instance(PackageRepositoryInterface::class, $this->packageRepositoryMock);
    }

    /**
     * @covers ::create
     *
     * @group DMS
     * @group DMS_PACKAGE
     */
    public function testCreate()
    {
        $params = ['some_params'];

        /** @var Package $package */
        $package = $this->getEloquentMock(Package::class);
        $package->id = PHP_INT_MAX;

        $this->packageRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once();

        $this->packageRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($params)
            ->andReturn($package);

        $this->packageRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once();

        Log::shouldReceive('info')
            ->with('Package has been successfully created', ['id' => $package->id]);

        /** @var PackageService $service */
        $service = $this->app->make(PackageService::class);

        $result = $service->create($params);

        $this->assertEquals($package, $result);
    }

    /**
     * @covers ::create
     *
     * @group DMS
     * @group DMS_PACKAGE
     */
    public function testCreateWithException()
    {
        $params = ['some_params'];
        $exception = new \Exception();

        /** @var Package $package */
        $package = $this->getEloquentMock(Package::class);
        $package->id = PHP_INT_MAX;

        $this->packageRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once();

        $this->packageRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($params)
            ->andThrow($exception);

        $this->packageRepositoryMock
            ->shouldReceive('commitTransaction')
            ->never();

        $this->packageRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->once();

        Log::shouldReceive('error')
            ->with('Package create error. Params - ' . json_encode($params), $exception->getTrace());

        $this->expectException(PackageException::class);

        /** @var PackageService $service */
        $service = $this->app->make(PackageService::class);

        $result = $service->create($params);

        $this->assertNull($result);
    }

    /**
     * @covers ::update
     *
     * @group DMS
     * @group DMS_PACKAGE
     */
    public function testUpdate()
    {
        $packageId = PHP_INT_MAX;

        $params = [
            'id' => $packageId,
            'some_params'
        ];

        /** @var Package $package */
        $package = $this->getEloquentMock(Package::class);
        $package->id = $packageId;

        $this->packageRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once();

        $this->packageRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($params)
            ->andReturn($package);

        $this->packageRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once();

        Log::shouldReceive('info')
            ->with('Package has been successfully updated', ['id' => $package->id]);

        /** @var PackageService $service */
        $service = $this->app->make(PackageService::class);

        $result = $service->update($packageId, $params);

        $this->assertEquals($package, $result);
    }

    /**
     * @covers ::update
     *
     * @group DMS
     * @group DMS_PACKAGE
     */
    public function testUpdateWithException()
    {
        $packageId = PHP_INT_MAX;

        $params = [
            'id' => $packageId,
            'some_params'
        ];

        $exception = new \Exception();

        /** @var Package $package */
        $package = $this->getEloquentMock(Package::class);
        $package->id = $packageId;

        $this->packageRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once();

        $this->packageRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($params)
            ->andThrow($exception);

        $this->packageRepositoryMock
            ->shouldReceive('commitTransaction')
            ->never();

        $this->packageRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->once();

        Log::shouldReceive('error')
            ->with('Package update error. Params - ' . json_encode($params), $exception->getTrace());

        $this->expectException(PackageException::class);

        /** @var PackageService $service */
        $service = $this->app->make(PackageService::class);

        $result = $service->update($packageId, $params);

        $this->assertNull($result);
    }

    /**
     * @covers ::delete
     *
     * @group DMS
     * @group DMS_PACKAGE
     */
    public function testDelete()
    {
        $packageId = PHP_INT_MAX;

        /** @var Package $package */
        $package = $this->getEloquentMock(Package::class);
        $package->id = PHP_INT_MAX;

        $this->packageRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once();

        $this->packageRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with(['id' => $packageId])
            ->andReturn(true);

        $this->packageRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once();

        Log::shouldReceive('info')
            ->with('Package has been successfully deleted', ['id' => $package->id]);

        /** @var PackageService $service */
        $service = $this->app->make(PackageService::class);

        $result = $service->delete($packageId);

        $this->assertTrue($result);
    }

    /**
     * @covers ::delete
     *
     * @group DMS
     * @group DMS_PACKAGE
     */
    public function testDeleteWithException()
    {
        $packageId = PHP_INT_MAX;
        $exception = new \Exception();

        /** @var Package $package */
        $package = $this->getEloquentMock(Package::class);
        $package->id = PHP_INT_MAX;

        $this->packageRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once();

        $this->packageRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with(['id' => $packageId])
            ->andThrow($exception);

        $this->packageRepositoryMock
            ->shouldReceive('commitTransaction')
            ->never();

        $this->packageRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->once();

        Log::shouldReceive('error')
            ->with('Package delete error. id - ' . $packageId, $exception->getTrace());

        /** @var PackageService $service */
        $service = $this->app->make(PackageService::class);

        $result = $service->delete($packageId);

        $this->assertFalse($result);
    }
}
