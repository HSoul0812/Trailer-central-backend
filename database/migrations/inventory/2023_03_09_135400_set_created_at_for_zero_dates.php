<?php

use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB as Query;
use App\Models\Inventory\Inventory;
use stdClass as Row;

class SetCreatedAtForZeroDates extends Migration
{

    private const ZERO_DATE = '0000-00-00 00:00:00';

    /** @var string when there is not a replaceable date this default date will be setup */
    private const DEFAULT_DATE = '2020-01-01 00:00:00';

    /**
     * Given some dealer websites are using range filters for `created_at` and we're setting up `0000-00-00 00:00:00` as NOW in
     * the ElasticSearch indexation, it is causing data discrepancies, so this method will setup a well enough `created_at` value.
     *
     * It will use an heuristic method to find a replaceable date for those `created_at` values like `0000-00-00 00:00:00`,
     * if the heuristic method wouldn't work, it will use the default date `2020-01-01 00:00:00`. Finally it will
     * reindex by dealers and invalidate their cache.
     *
     * @return void
     */
    public function up(): void
    {
        /** @var InventoryServiceInterface $service */
        $service = app(InventoryServiceInterface::class);

        $heuristicDates = <<<SQL
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id - 1) AS created_at_1,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id - 5) AS created_at_2,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id - 10) AS created_at_3,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id - 20) AS created_at_4,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id - 30) AS created_at_5,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id - 40) AS created_at_6,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id - 50) AS created_at_7,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id - 60) AS created_at_8,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id - 70) AS created_at_9,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id - 80) AS created_at_10,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id - 90) AS created_at_11,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id - 100) AS created_at_12,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id + 1) AS created_at_13,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id + 10) AS created_at_14,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id + 20) AS created_at_15,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id + 30) AS created_at_16,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id + 40) AS created_at_17,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id + 50) AS created_at_18,
           (SELECT _i.created_at FROM inventory _i WHERE _i.inventory_id = i.inventory_id + 60) AS created_at_29
SQL;

        $inventories = Query::table('inventory AS i')
            ->select('i.inventory_id', 'i.dealer_id')
            ->selectRaw($heuristicDates)
            ->where('created_at', '=', self::ZERO_DATE)
            ->orderBy('i.inventory_id')
            ->cursor();

        $dealerIds = [];

        $inventories->each(function (Row $inventory) use (&$dealerIds): void {
            $heuristicDate = collect($inventory)->except(['inventory_id', 'dealer_id'])->max();

            $replaceableDate = $heuristicDate === null || $heuristicDate === self::ZERO_DATE || $heuristicDate === '' ?
                self::DEFAULT_DATE :
                $heuristicDate;

            Inventory::where('inventory_id', $inventory->inventory_id)->update(['created_at' => $replaceableDate]);

            $dealerIds[$inventory->dealer_id] = $inventory->dealer_id;
        });

        if (count($dealerIds) > 0) {
            $service->invalidateCacheAndReindexByDealerIds($dealerIds);
        }
    }
}
