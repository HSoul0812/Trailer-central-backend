<?php
declare(strict_types=1);

namespace Tests\Integration\Repositories\Dms;

use App\Models\User\DealerLocation;
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

    private const OVERLAY_DEFAULT_CONFIGURATION = [
        'overlay_logo' => 'logo.png',
        'overlay_logo_position' => User::OVERLAY_LOGO_POSITION_LOWER_RIGHT,
        'overlay_logo_width' => '20%',
        'overlay_logo_height' => '20%',
        'overlay_upper' => User::OVERLAY_UPPER_DEALER_NAME,
        'overlay_upper_bg' => '#000000',
        'overlay_upper_alpha' => 0,
        'overlay_upper_text' => '#ffffff',
        'overlay_upper_size' => 40,
        'overlay_upper_margin' => 40,
        'overlay_lower' => User::OVERLAY_UPPER_DEALER_PHONE,
        'overlay_lower_bg' => '#000000',
        'overlay_lower_alpha' => 0,
        'overlay_lower_text' => '#ffffff',
        'overlay_lower_size' => 40,
        'overlay_lower_margin' => 40,
        'overlay_enabled' => Inventory::OVERLAY_ENABLED_ALL
    ];

    /** @var int */
    protected $dealerId;

    /** @var User  */
    protected $dealer;

    public function setUp(): void
    {
        parent::setUp();

        $this->dealer = factory(User::class)->create(self::OVERLAY_DEFAULT_CONFIGURATION);
        $this->dealerId = $this->dealer->dealer_id;
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

    /**
     * @covers ::getOverlayParams
     *
     * @group DMS
     * @group DMS_INVENTORY
     */
    public function testGetOverlayParams()
    {
        /** @var Inventory $inventory */
        $inventory = factory(Inventory::class)->create([
                'dealer_id' => $this->dealerId,
                'title' => '**some_random__** Test Inventory 2021 Trailers',
                'overlay_enabled' => Inventory::OVERLAY_ENABLED_ALL
            ]
        );

        $expectedResult = array_merge(
            self::OVERLAY_DEFAULT_CONFIGURATION,
            [
                'overlay_default' => null,
                'dealer_id' => $this->dealer->dealer_id,
                'inventory_id' => $inventory->inventory_id,
                'dealer_overlay_enabled' => $this->dealer->overlay_enabled,
                'overlay_text_dealer' => $this->dealer->name,
                'overlay_updated_at' => $this->dealer->overlay_updated_at,
                'overlay_text_phone' => DealerLocation::phoneWithNationalFormat(
                    $inventory->dealerLocation->phone,
                    $inventory->dealerLocation->country
                ),
                'overlay_text_location' => sprintf(
                    '%s, %s',
                    $inventory->dealerLocation->city,
                    $inventory->dealerLocation->region
                )
            ]
        );

        /** @var InventoryRepository $inventoryRepository */
        $inventoryRepository = app()->make(InventoryRepository::class);

        $overlayConfiguration = $inventoryRepository->getOverlayParams($inventory->inventory_id);

        $this->assertEquals($expectedResult, $overlayConfiguration);
    }
}
