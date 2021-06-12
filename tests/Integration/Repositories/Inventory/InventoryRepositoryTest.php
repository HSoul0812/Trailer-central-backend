<?php
declare(strict_types=1);

namespace Tests\Integration\Repositories\Dms;

use App\Models\User\User;
use App\Models\Inventory\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Repositories\Inventory\InventoryRepository;
use Illuminate\Support\Facades\DB;

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
        $this->dealerId = factory(User::class)->create()->dealer_id;
    }

    public function tearDown(): void
    {
        User::where('dealer_id', $this->dealerId)->delete();
        Inventory::where('dealer_id', $this->dealerId)->delete();
        $this->dealerId = null;
        parent::tearDown();
    }


    /**
     * @covers ::getFloorplanned
     */
    public function testGetFloorplannedInventoryWithStatus()
    {
        $itemHaveStatus = [
            'dealer_id' => $this->dealerId,
            'status' => Inventory::STATUS_SOLD,
            'is_floorplan_bill' => 1,
            'active' => 1 ,
            'fp_vendor' => 100,
            'true_cost' => 1000,
            'fp_balance' => 1200,
            'bill_id' => 12345,
            'notes' => 'floorplan item'
        ];

        factory(Inventory::class)->create($itemHaveStatus);

        /** @var InventoryRepository $inventoryRepository */
        $inventoryRepository = $this->app->make(InventoryRepository::class);
        $result = $inventoryRepository->getFloorplannedInventory(['dealer_id' => $this->dealerId]);
        $this->assertEquals(1, count($result));

        $this->assertEquals($itemHaveStatus['fp_balance'], $result[0]['fp_balance']);
    }

    /**
     * @covers ::getFloorplanned
     */
    public function testGetFloorplannedInventoryWithoutStatus()
    {
        $itemHaveNoStatus = [
            'dealer_id' => $this->dealerId,
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

        /** @var InventoryRepository $inventoryRepository */
        $inventoryRepository = app()->make(InventoryRepository::class);
        $result = $inventoryRepository->getFloorplannedInventory(['dealer_id' => $this->dealerId]);
        $this->assertEquals(0, count($result));
    }
}
