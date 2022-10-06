<?php

namespace App\Console\Commands\Inventory;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use App\Models\Inventory\Inventory;
use Laravel\Scout\EngineManager;

class RecreateInventoryIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:recreate-inventory-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $inventory_name = 'inventory_' . Date::now()->format("YmdHm");
        $engine = app(EngineManager::class);

        $engine->safeSyncImporter(new Inventory, $inventory_name);
    }
}
