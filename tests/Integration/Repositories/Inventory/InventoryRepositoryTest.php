<?php
declare(strict_types=1);

namespace Tests\Integration\Repositories\Dms;

use App\Models\User\User;
use App\Models\Inventory\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Repositories\Inventory\InventoryRepository;
use \Illuminate\Database\QueryException;

/**
 * @group DW
 * @group DW_INVENTORY
 *
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
     *
     * @group DMS
     * @group DMS_INVENTORY
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
     *
     * @group DMS
     * @group DMS_INVENTORY
     */
    public function testWillThrowAnExceptionWhenStatusNull()
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches("/1048 Column 'status' cannot be null/s");

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

    /**
     * @covers ::getAll
     *
     * @group DMS
     * @group DMS_INVENTORY
     */
    public function testGetAllMatchingAllWordsInSearchTerm()
    {
        factory(Inventory::class)->create([
            'dealer_id' => $this->dealerId,
            'title' => '**some_random__** Test Inventory 2021 Trailers'
        ]);
        factory(Inventory::class)->create([
            'dealer_id' => $this->dealerId,
            'title' => '**some_random__** 2022 Green Trailers'
        ]);
        factory(Inventory::class)->create([
            'dealer_id' => $this->dealerId,
            'title' => '**some_random__** Red Trailer Made In 2020'
        ]);

        /** @var InventoryRepository $inventoryRepository */
        $inventoryRepository = app()->make(InventoryRepository::class);

        // Just to be sure we are dealing with only the inventory we created in this test
        $inventory = $inventoryRepository->getAll([
            'dealer_id' => $this->dealerId,
            'search_term' => '**some_random__**'
        ]);

        $this->assertEquals(3, $inventory->count());

        // This should match all inventory we created because they all have the words `**some_random__** & Trailer`
        $inventory = $inventoryRepository->getAll([
            'dealer_id' => $this->dealerId,
            'search_term' => '**some_random__** Trailer'
        ]);

        $this->assertEquals(3, $inventory->count());

        // This should match only 1 inventory because only 1 inventory has `Red & Made`
        $inventory = $inventoryRepository->getAll([
            'dealer_id' => $this->dealerId,
            'search_term' => '**some_random__** Red Made'
        ]);

        $this->assertEquals(1, $inventory->count());

        // This should match only 1 inventory because only 1 inventory has `2022 & Trailers`
        $inventory = $inventoryRepository->getAll([
            'dealer_id' => $this->dealerId,
            'search_term' => '**some_random__** 2022 Trailers'
        ]);

        $this->assertEquals(1, $inventory->count());

        // This should match no inventory because all the inventory start with `**some_random__**`
        $inventory = $inventoryRepository->getAll([
            'dealer_id' => $this->dealerId,
            'search_term' => 'Trailers **some_random__**'
        ]);

        $this->assertEquals(0, $inventory->count());
    }
}
