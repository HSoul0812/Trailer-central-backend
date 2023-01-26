<?php

namespace App\Console\Commands\Inventory;

use App\Jobs\ElasticSearch\Cache\InvalidateCacheJob;
use App\Models\Inventory\Inventory;
use Illuminate\Console\Command;

class RecreateInventoryIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:recreate-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will recreate the inventory ES index using an aliasing strategy';

    /**
     * Execute the console command.
     *
     * @throws \Exception when some unknown error has been thrown
     */
    public function handle(): void
    {
        Inventory::makeAllSearchableUsingAliasStrategy();

        $patternToCleanAll = 'inventories.*';

        // no matter if cache is disabled, invalidating the entire cache should be done
        dispatch(new InvalidateCacheJob([$patternToCleanAll]));

        $this->output->writeln(sprintf('InvalidateCacheJob was dispatched using the pattern: %s', $patternToCleanAll));
    }
}
