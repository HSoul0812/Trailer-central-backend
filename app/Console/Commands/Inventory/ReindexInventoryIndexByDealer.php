<?php

namespace App\Console\Commands\Inventory;

use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Console\Command;

/**
 * Once the integration team has moved everything (inventory related) to the API side, then this command should be removed
 */
class ReindexInventoryIndexByDealer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:reindex-by-dealer {dealer_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will reindex the inventory ES index by dealer id using queue workers';

    public function handle(InventoryServiceInterface $service): void
    {
        $service->invalidateCacheAndReindexByDealerIds([$this->argument('dealer_id')]);
    }
}
