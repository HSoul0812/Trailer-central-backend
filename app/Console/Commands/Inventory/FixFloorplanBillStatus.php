<?php

namespace App\Console\Commands\Inventory;

use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Console\Command;
use App\Models\Inventory\Inventory;
use Grimzy\LaravelMysqlSpatial\Eloquent\Builder as GrimzyBuilder;

/**
 * Class FixFloorplanBillStatus
 * @package App\Console\Commands\Inventory
 */
class FixFloorplanBillStatus extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "inventory:fix-floorplan-bill-status {dealer_id?}";

    /**
     * @todo this business logic should be moved to the service itself
     *
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var InventoryServiceInterface $service */
        $service = app(InventoryServiceInterface::class);

        /** @var Inventory[] $inventories */
        $inventories = $this->getMalformedFloorplannedInventoryQuery()->get();

        $dealerIds = Inventory::withoutCacheInvalidationAndSearchSyncing(function () use ($inventories): array {
            $dealerIds = [];

            foreach ($inventories as $inventory) {
                $inventory->is_floorplan_bill = Inventory::IS_FLOORPLANNED;
                $inventory->save();
                $this->info("Updated {$inventory->stock} is_floorplan_bill value to 1");

                $dealerIds[$inventory->dealer_id] = $inventory->dealer_id;
            }

            return $dealerIds;
        });

        foreach ($dealerIds as $dealerId){
            $service->invalidateCacheAndReindexByDealerIds([$dealerId]);
        }

        return true;
    }

    private function getMalformedFloorplannedInventoryQuery(): GrimzyBuilder
    {
        $dealerId = $this->argument('dealer_id');

        $query = Inventory::whereNotNull('bill_id')
                            ->whereNotNull('fp_balance' )
                            ->whereNotNull('fp_committed')
                            ->whereNotNull('fp_balance')
                            ->whereNotNull('fp_vendor')
                            ->where('fp_vendor', '!=', 0)
                            ->where('is_archived', Inventory::IS_NOT_ARCHIVED);

        if ($dealerId) {
            $query->where('inventory.dealer_id', $dealerId);
        }

        $query->join('qb_bills', 'qb_bills.id', '=', 'inventory.bill_id');

        $query->where('is_floorplan_bill', Inventory::IS_NOT_FLOORPLANNED);

        return $query;
    }
}
