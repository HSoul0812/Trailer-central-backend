<?php

namespace App\Console\Commands\Inventory;

use App\Models\Inventory\InventoryFeature;
use Illuminate\Console\Command;

class FixInventoryFeatures extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "inventory:fix-features";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "This command will fix the corrupted data of inventory_feature table where invalid feature_list_id is inserted.";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $corruptedDataCount = InventoryFeature::where('feature_list_id', 0)->count();

        $this->info('Found ' . $corruptedDataCount . ' wrong inventory features data!');

        InventoryFeature::where('feature_list_id', 0)->delete();

        $this->info('inventory_feature table is fixed!');
    }
}
