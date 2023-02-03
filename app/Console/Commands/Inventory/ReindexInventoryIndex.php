<?php

namespace App\Console\Commands\Inventory;

use App\Models\Inventory\Inventory;
use Illuminate\Console\Command;

/**
 * Once the integration team has moved everything (inventory related) to the API side, then this command should be removed
 */
class ReindexInventoryIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:reindex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will reindex the inventory ES index using queue workers';

    public function handle(): void
    {
        $this->call('scout:import', ['model' => Inventory::class]);
    }
}
