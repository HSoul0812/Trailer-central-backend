<?php

namespace App\Console\Commands\Inventory;

use App\Services\Inventory\InventoryServiceInterface;
use App\Services\User\GeoLocationServiceInterface;
use Illuminate\Console\Command;
use App\Models\Inventory\Inventory;

/**
 * Class FixGeolocationByDealer
 * @package App\Console\Commands\Inventory
 */
class FixGeolocationByDealer extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "inventory:fix-geolocation-by-dealer {dealer_id?}";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(GeoLocationServiceInterface $geoService, InventoryServiceInterface $inventoryService)
    {
        $dealerId = $this->argument('dealer_id');

        $inventories = Inventory::where('dealer_id', $dealerId)->get();

        foreach ($inventories as $inventory) {

            $geoLocationPoint = $geoService->geoPointFromZipCode($inventory->dealerLocation->postalcode);

            Inventory::withoutCacheInvalidationAndSearchSyncing(function () use ($geoLocationPoint, $inventory): void {
                if($geoLocationPoint){
                    $inventory->geolocation = $geoLocationPoint;
                    $inventory->save();

                    $this->info("Saved Inventory: {$inventory->inventory_id}");
                }
            });
        }

        if ($dealerId) {
            $inventoryService->invalidateCacheAndReindexByDealerIds([$dealerId]);
        }

        return true;
    }
}
