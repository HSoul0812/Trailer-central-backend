<?php

namespace App\Console\Commands\Inventory;

use App\Models\Inventory\Inventory;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\RedisResponseCacheKey;
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
    public function handle(InventoryResponseCacheInterface $responseCache): void
    {
        Inventory::makeAllSearchableUsingAliasStrategy();

        // no matter if cache is disabled, invalidating the entire cache should be done
        $responseCache->forget([RedisResponseCacheKey::CLEAR_ALL_PATTERN]);

        $this->line(sprintf(
                'InvalidateCacheJob was dispatched using the pattern: <comment>%s</comment>',
                RedisResponseCacheKey::CLEAR_ALL_PATTERN
            )
        );
    }
}
