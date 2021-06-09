<?php

namespace Tests\Integration\Repositories\Dms;

use App\Models\User\DealerLocation;
use App\Models\User\User;
use App\Models\Inventory\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Repositories\Inventory\InventoryRepository;

/**
 * Class InventoryRepositoryTest
 * @package Tests\Integration\Repositories\Dms
 *
 * @coversDefaultClass \App\Repositories\Inventory\InventoryRepository
 */
class InventoryRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    protected $dealerId;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerId = null;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->dealerId = null;
    }


    /**
     * @covers ::getFloorplanned
     */
    public function testGetFloorplannedInventoryWithStatus()
    {
        $itemHaveStatus = [
            'status' => Inventory::STATUS_SOLD,
            'is_floorplan_bill' => 1,
            'active' => 1 ,
            'fp_vendor' => 100,
            'true_cost' => 1000,
            'fp_balance' => 1200,
            'bill_id' => 12345,
            'notes' => 'floorplan item'
        ];

        $test = factory(Inventory::class)->create($itemHaveStatus);
        $dealerId = $test->dealer_id;

        /** @var InventoryRepository $inventoryRepository */
        $inventoryRepository = $this->app->make(InventoryRepository::class);
        $result = $inventoryRepository->getFloorplannedInventory(['dealer_id' => $dealerId]);
        $this->assertEquals(1, count($result));

        $this->assertEquals($itemHaveStatus['fp_balance'], $result[0]['fp_balance']);
    }

    /**
     * @covers ::getFloorplanned
     */
    public function testGetFloorplannedInventoryWithoutStatus()
    {
        $itemHaveNoStatus = [
            'status' => null,
            'is_floorplan_bill' => 1,
            'active' => 1 ,
            'fp_vendor' => 50,
            'true_cost' => 500,
            'bill_id' => 12345,
            'fp_balance' => 3400,
            'notes' => 'floorplan item'
        ];

        $test = factory(Inventory::class)->create($itemHaveNoStatus);
        $dealerId = $test->dealer_id;

        /** @var InventoryRepository $inventoryRepository */
        $inventoryRepository = app()->make(InventoryRepository::class);
        $result = $inventoryRepository->getFloorplannedInventory(['dealer_id' => $dealerId]);
        $this->assertEquals(0, count($result));
    }
}
