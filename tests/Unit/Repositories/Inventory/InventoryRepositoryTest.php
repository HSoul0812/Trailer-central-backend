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
 * @group DW
 * @group DW_INVENTORY
 *
 * @coversDefaultClass \App\Repositories\Inventory\InventoryRepository
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
}
