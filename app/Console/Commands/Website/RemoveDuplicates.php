<?php

namespace App\Console\Commands\Website;

use Illuminate\Console\Command;
use App\Models\Inventory\Inventory;


/**
 * Takes the data from a CSV in the following format:
 * 
 * redirect_from,redirect_to
 * google.com,google.com/a
 * google.com/b,google.com/c
 * google.com/d,google.com/e
 * 
 * And imports it into the website_redirect table
 */
class RemoveDuplicates extends Command {
        
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "website:remove-duplicates {dealer-id}";
    
    /**
     *
     * @var int
     */
    private $dealerId;
        
    public function __construct() {       
        parent::__construct();        
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    { 
        $this->dealerId = $this->argument('dealer-id');
        
        $inventories = Inventory::where('dealer_id', $this->dealerId)
                    ->where('is_archived', 0)
                    ->get();
        
        foreach($inventories as $inventory) {
            $dupInventory = Inventory::where('stock', $inventory->stock)
                                    ->where('is_archived', 0)
                                    ->where('dealer_id', $this->dealerId)
                                    ->get();
            
            if (count($dupInventory) > 1) {
                $inventory->delete();
                $this->info("Delete 1 {$inventory->stock}");
            }
            
        }
                    
    }
}
