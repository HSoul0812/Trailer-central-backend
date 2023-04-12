<?php

namespace App\Console\Commands\Inventory;

use Illuminate\Console\Command;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\Inventory;

class CorrectInventoryCountsFromBackup extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "
        inventory:correct-inventory-counts-from-backup
        {backup_db : The backup database hostname.}
        {before_date : The date to factor out.}
        {--dealer_id= : dealer id we wish to apply this to.}
    "; 

    protected $description = 'Compares inventory counts against the backup_db and archives any inventory present in the current DB but not present in the backup DB';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        config(['database.connections.backup_mysql.host' => $this->argument('backup_db')]);
        
        $onlyDealer = $this->option('dealer_id');
        if ($onlyDealer) {
            $dealers = User::where('dealer_id', $onlyDealer)->get();
        } else {
            $dealers = User::all();
        }
        
        foreach($dealers as $dealer) {
            $inventories = $dealer->inventories()
                    ->where('is_archived', 0)
                    ->where('created_at', '<', $this->argument('before_date') . ' 00:00:00')
                    ->cursor();
            foreach($inventories as $inventory) {                
                if (!$this->inventoryExistsInBackup($dealer->dealer_id, $inventory->stock)) {
                    $this->info("Archiving unit {$inventory->stock} for dealer id {$dealer->dealer_id}"); 
                    Inventory::withoutSyncingToSearch(function () use ($inventory) {
                        Inventory::query()
                                ->where('inventory_id', $inventory->inventory_id)
                                ->update([
                                    'archived_at' => now(),
                                    'is_archived' => Inventory::IS_ARCHIVED,
                                    'active' => Inventory::IS_NOT_ACTIVE
                                ]);
                    });
                }
            } 
            
        }
       

        return true;
    }
    
    private function inventoryExistsInBackup(int $dealerId, string $stock): bool
    {
        return DB::connection('backup_mysql')
                    ->table('inventory')
                    ->where('dealer_id', $dealerId)
                    ->where('stock', $stock)
                    ->where('is_archived', Inventory::IS_NOT_ARCHIVED)
                    ->exists();
    }
}
