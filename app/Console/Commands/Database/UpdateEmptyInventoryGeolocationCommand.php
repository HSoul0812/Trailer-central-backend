<?php

namespace App\Console\Commands\Database;

use App\Models\User\User;
use App\Services\Inventory\InventoryServiceInterface;
use DB;
use Illuminate\Console\Command;

/**
 * Use this command to update all the empty geolocation value to POINT(0,0)
 *
 * It's IMPORTANT to note that we can't do this in the MySQL version 8 (empty point causes a problem when update)
 */
class UpdateEmptyInventoryGeolocationCommand extends Command
{
    protected $signature = 'database:inventory:update-empty-geolocation';

    protected $description = 'Update the empty geolocation to default as POINT(0,0)';

    public function handle(InventoryServiceInterface $service): int
    {
        $dealerIds = User::whereHas('inventories')->pluck('dealer_id');

        $totalUpdatedRow = 0;
        foreach ($dealerIds as $dealerId) {
            $totalUpdatedRow += $this->updateForDealerId($dealerId);
            $service->invalidateCacheAndReindexByDealerIds([$dealerId]);
        }

        $this->info("All the empty geolocations are now being updated to POINT(0,0)!");
        $this->info("Total updated row: $totalUpdatedRow");

        return 0;
    }

    private function updateForDealerId($dealerId): int
    {
        // since this is not using event observers we dont need to avoid handling triggered saving events
        $updatedRow = DB::update("update inventory set geolocation = POINT(0, 0) where dealer_id = ? and trim(geolocation) = ''", [$dealerId]);

        if ($updatedRow !== 0) {
            $this->line("Dealer ID: $dealerId, Updated $updatedRow rows.");
        }

        return $updatedRow;
    }
}
