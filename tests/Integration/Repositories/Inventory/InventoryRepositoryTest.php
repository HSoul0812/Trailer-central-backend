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

    /**
     * @covers ::getFloorplanned
     */
    public function testGetFloorplannedInventoryWithStatus()
    {
        $this->prepareDB();
        $dealerID = self::getTestDealerId();
        $itemHaveStatus = [
            'dealer_id' => $dealerID,
            'dealer_location_id' => self::getTestDealerLocationId(),
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

        /** @var InventoryRepository $inventoryService */
        $inventoryService = $this->app->make(InventoryRepository::class);
        $result = $inventoryService->getFloorplannedInventory(['dealer_id' => $dealerID]);
        $this->assertEquals(1, count($result));

        $this->assertEquals($itemHaveStatus['fp_balance'], $result[0]['fp_balance']);
        $this->revertDB();
    }

    /**
     * @covers ::getFloorplanned
     */
    public function testGetFloorplannedInventoryWithoutStatus()
    {
        $this->prepareDB();
        $dealerID = self::getTestDealerId();
        $itemHaveNoStatus = [
            'dealer_id' => $dealerID,
            'dealer_location_id' => self::getTestDealerLocationId(),
            'status' => null,
            'is_floorplan_bill' => 1,
            'active' => 1 ,
            'fp_vendor' => 50,
            'true_cost' => 500,
            'bill_id' => 12345,
            'fp_balance' => 3400,
            'notes' => 'floorplan item'
        ];

        factory(Inventory::class)->create($itemHaveNoStatus);

        /** @var InventoryRepository $inventoryService */
        $inventoryService = app()->make(InventoryRepository::class);
        $result = $inventoryService->getFloorplannedInventory(['dealer_id' => $dealerID]);
        $this->assertEquals(0, count($result));
        $this->revertDB();
    }

    protected function revertDB() {
        Inventory::where('dealer_id', self::getTestDealerId())->delete();
        User::where('dealer_id', self::getTestDealerId())->delete();
        DealerLocation::where('dealer_id', self::getTestDealerId())->delete();
    }

    protected function prepareDB() {
        factory(User::class)->create([
            'dealer_id' => self::getTestDealerId()
        ]);
        factory(DealerLocation::class)->create([
            'dealer_id' => self::getTestDealerId(),
            'dealer_location_id' => self::getTestDealerLocationId()
        ]);
    }
}
