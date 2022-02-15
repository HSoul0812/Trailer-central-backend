<?php

declare(strict_types=1);

namespace Tests\database\seeds\User;

use App\Models\Feed\Mapping\Incoming\ApiEntityReference;
use App\Models\Inventory\Inventory;
use App\Models\User\DealerLocation;
use App\Models\User\DealerLocationMileageFee;
use App\Models\User\DealerLocationQuoteFee;
use App\Models\User\DealerLocationSalesTax;
use App\Models\User\DealerLocationSalesTaxItemV1;
use Illuminate\Database\Eloquent\Collection;
use Tests\database\seeds\Seeder;
use App\Models\User\User;
use App\Traits\WithGetter;
use DB;

/**
 * @property-read Collection<User>|array<User> $dealers
 * @property-read array<int, Collection<DealerLocation>|array<DealerLocation>> $locations
 */
class DealerLocationSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var Collection<User>|array<User>
     */
    private $dealers;

    /**
     * @var array<int, Collection<DealerLocation>|array<DealerLocation>>
     */
    private $locations;

    public function __construct()
    {
        $this->dealers = Collection::make([]);
        $this->locations = [];
    }

    public function seedDealers(): void
    {
        $this->dealers = factory(User::class, 3)->create();
    }

    public function seed(): void
    {
        $this->seedDealers();

        $dealer1Id = $this->dealers[0]->getKey();
        $dealer2Id = $this->dealers[1]->getKey();
        $dealer3Id = $this->dealers[2]->getKey();

        // 9 new dealer locations
        $this->locations[$dealer1Id] = factory(DealerLocation::class, 8)->create([
            'dealer_id' => $dealer1Id,
        ]);
        $this->locations[$dealer1Id]->add(factory(DealerLocation::class)->create([
            'dealer_id' => $dealer1Id,
            'name' => 'Springfield XXX',
            'is_default' => 1,
            'is_default_for_invoice' => 1
        ]));

        $firstInventoryId = $this->locations[$dealer1Id]->first()->dealer_location_id;

        factory(DealerLocationMileageFee::class)->create([
            'dealer_location_id' => $firstInventoryId,
            'inventory_category_id' => 2,
        ]);

        // 5 new inventories
        factory(Inventory::class, 5)->create([
            'dealer_id' => $dealer1Id,
            'dealer_location_id' => $firstInventoryId
        ]);
        // 5 new entity references
        factory(ApiEntityReference::class, 3)->create([
            'entity_type' => ApiEntityReference::TYPE_LOCATION,
            'entity_id' => $firstInventoryId
        ]);

        // 5 new dealer locations  for the third dealer
        $this->locations[$dealer2Id] = factory(DealerLocation::class, 4)->create([
            'dealer_id' => $dealer2Id
        ]);
        $this->locations[$dealer2Id]->add(factory(DealerLocation::class)->create([
            'dealer_id' => $dealer2Id,
            'name' => 'Shelbyville YYY',
            'city' => 'Shelbyville XXX',
            'is_default' => 1,
            'is_default_for_invoice' => 1
        ]));
        // 6 new dealer locations for the third dealer
        $this->locations[$dealer3Id] = factory(DealerLocation::class, 6)->create([
            'dealer_id' => $dealer3Id,
            'contact' => 'Nelson Muntz'
        ]);
    }

    public function cleanUp(): void
    {
        $dealersId = $this->dealers->pluck('dealer_id');
        $locationsId = collect($this->locations)->pluck('*.dealer_location_id')->collapse();

        DB::table(ApiEntityReference::getTableName())
            ->where('entity_type', ApiEntityReference::TYPE_LOCATION)
            ->whereIn('entity_id', $locationsId)
            ->delete();

        DB::table(Inventory::getTableName())
            ->whereIn('dealer_id', $dealersId)
            ->delete();

        DB::table(DealerLocationSalesTax::getTableName())
            ->whereIn('dealer_location_id', $locationsId)
            ->delete();

        DB::table(DealerLocationSalesTaxItemV1::getTableName())
            ->whereIn('dealer_location_id', $locationsId)
            ->delete();

        DB::table(DealerLocationQuoteFee::getTableName())
            ->whereIn('dealer_location_id', $locationsId)
            ->delete();

        DB::table(DealerLocationMileageFee::getTableName())
            ->whereIn('dealer_location_id', $locationsId)
            ->delete();

        DB::table(DealerLocation::getTableName())
            ->whereIn('dealer_id', $dealersId)
            ->delete();

        User::whereIn('dealer_id', $dealersId)->delete();
    }
}
