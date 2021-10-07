<?php

namespace Tests\Unit\Repositories\Inventory;


use App\Repositories\Inventory\InventoryRepository;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Inventory\Inventory;
use Mockery;
use Tests\TestCase;

/**
 * @coversDefaultClass App\Repositories\Inventory\InventoryRepository
 */
class InventoryRepositoryTest extends TestCase
{

    /**
     * @var LegacyMockInterface|InventoryRepositoryInterface
     */
    private $inventoryRepositoryMock;

    /**
     * @var App\Models\Inventory\Inventory
     */
    private $inventoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryRepositoryMock = Mockery::mock(InventoryRepositoryInterface::class);
        $this->app->instance(InventoryRepositoryInterface::class, $this->inventoryRepositoryMock);

        $this->inventoryMock = $this->getEloquentMock(Inventory::class);
        $this->app->instance(Inventory::class, $this->inventoryMock);
    }

    /**
     * @covers ::getAll
     */
    public function testGetAllDefault(): void
    {
        $serviceRepoParams = [
            'id' =>  $this->serviceOrderMock->id,
            'status' => ServiceOrder::SERVICE_ORDER_STATUS['picked_up']
        ];

        $this->serviceOrderMock
                ->shouldReceive('save')
                ->once()
                ->andReturn(new ServiceOrder([
                    'id' => $this->serviceOrderMock->id,
                    'status' => ServiceOrder::SERVICE_ORDER_STATUS['ready_for_pickup']
                ]));

        $this->serviceOrderMock
                ->shouldReceive('fill')
                ->once()
                ->with($serviceRepoParams);

        $this->serviceOrderMock
                ->shouldReceive('findOrFail')
                ->once()
                ->with($this->serviceOrderMock->id)
                ->andReturn($this->serviceOrderMock);


        $serviceRepo = $this->app->make(ServiceOrderRepository::class);

        $result = $serviceRepo->update($serviceRepoParams);
        $this->assertEquals($result->id, $this->serviceOrderMock->id);
    }

}
