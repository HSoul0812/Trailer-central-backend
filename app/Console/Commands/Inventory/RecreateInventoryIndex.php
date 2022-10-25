<?php

namespace App\Console\Commands\Inventory;

use App\Models\Inventory\Inventory;
use Illuminate\Console\Command;

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
     * @throws \Exception when some unknown error has been thrown
     */
    public function handle(): void
    {
        Inventory::makeAllSearchableUsingAliasStrategy();
    }
}
