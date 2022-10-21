<?php

namespace App\Console\Commands\CRM\Dms\BackupRecovery;

use App\Models\CRM\Dms\UnitSale;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
use DB;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class RestoreInventoryIdForQuotes extends Command
{
    const CHUNK_SIZE = 1000;

    protected $signature = '
        crm:dms:restore-inventory-id-for-quotes
        {dealer_id : The dealer ID.}
        {backup_db : The backup database hostname.}
    ';

    protected $description = 'Restore the inventory id for quotes from the backup database';

    /** @var callable */
    private static $beforeFetchInventoryFromCurrentDbCallback;

    public function handle(): int
    {
        $dealerId = (int) $this->argument('dealer_id');

        config(['database.connections.backup_mysql.host' => $this->argument('backup_db')]);

        try {
            $this->validate($dealerId);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
            return 1;
        }

        $this->restore($dealerId);

        return 0;
    }

    /**
     * @param int $dealerId
     *
     * @return void
     * @throws Exception
     */
    private function validate(int $dealerId)
    {
        $validDealer = User::where('dealer_id', $dealerId)->exists();
        if (!$validDealer) {
            throw new Exception("Dealer ID $dealerId not found.");
        }
    }

    /**
     * @param int $dealerId
     * @return void
     */
    private function restore(int $dealerId)
    {
        $this->info("Start restoring inventory_id for quotes for Dealer ID $dealerId!");

        $totalRestored = 0;

        // Loop through the backup DB, get the vin of inventory and use that data
        // to read the new inventory_id from the current database
        DB::connection('backup_mysql')
            ->table(UnitSale::getTableName(), 'us')
            ->select(['us.id', 'us.dealer_id', 'us.inventory_id', 'i.vin'])
            ->where('us.dealer_id', $dealerId)
            ->join(Inventory::getTableName() . ' as i', 'us.inventory_id', '=', 'i.inventory_id')
            ->chunkById(self::CHUNK_SIZE, function (Collection $unitSalesFromBackupDb) use (&$totalRestored) {
                $unitSalesFromBackupDb = $unitSalesFromBackupDb->keyBy('vin');
                $vins = $unitSalesFromBackupDb->keys()->toArray();

                // Give a chance for the unit test to hook into anything here
                if (is_callable(self::$beforeFetchInventoryFromCurrentDbCallback)) {
                    call_user_func(self::$beforeFetchInventoryFromCurrentDbCallback, $vins);
                }

                $inventoriesFromCurrentDb = DB::table(Inventory::getTableName())
                    ->select(['inventory_id', 'vin'])
                    ->whereIn('vin', $vins)
                    ->get()
                    ->keyBy('vin');

                // We'll perform transaction commit per chunk
                // this way, there won't be a chance where we
                // get the 'too many placeholders' error.
                DB::beginTransaction();

                foreach ($unitSalesFromBackupDb as $vin => $unitSaleFromBackupDb) {
                    // Skip this loop if the current DB doesn't
                    // have the VIN from the backup DB
                    if (!$inventoriesFromCurrentDb->has($vin)) {
                        $this->line("Cannot find VIN $vin from the current DB, skipping the Unit Sale ID $unitSaleFromBackupDb->id.");
                        continue;
                    }

                    $inventoryFromCurrentDb = $inventoriesFromCurrentDb->get($vin);

                    // We don't want to run the update command if the unit sale is already storing
                    // the correct inventory_id data
                    if ($unitSaleFromBackupDb->inventory_id === $inventoryFromCurrentDb->inventory_id) {
                        $this->line("Unit Sale ID $unitSaleFromBackupDb->id already has the correct inventory_id as $unitSaleFromBackupDb->inventory_id, skipping this one.");
                        continue;
                    }

                    // Update the current data using the matching vin's inventory_id
                    DB::table(UnitSale::getTableName())
                        ->where('id', $unitSaleFromBackupDb->id)
                        ->update([
                            'inventory_id' => $inventoryFromCurrentDb->inventory_id,
                        ]);

                    $message = sprintf(
                        "Unit Sale ID %s has updated inventory_id from %d to %d.",
                        $unitSaleFromBackupDb->id,
                        $unitSaleFromBackupDb->inventory_id,
                        $inventoryFromCurrentDb->inventory_id
                    );

                    $this->line($message);

                    $totalRestored++;
                }

                DB::commit();
            });

        $this->info("The command has successfully restored the inventory_id for $totalRestored records!");
    }

    /**
     * @param callable $beforeFetchInventoryFromCurrentDbCallback
     */
    public static function withBeforeFetchInventoryFromCurrentDbCallback(callable $beforeFetchInventoryFromCurrentDbCallback)
    {
        self::$beforeFetchInventoryFromCurrentDbCallback = $beforeFetchInventoryFromCurrentDbCallback;
    }
}
