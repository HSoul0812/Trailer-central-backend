<?php

namespace App\Console\Commands\Inventory;

use App\Jobs\Job;
use App\Models\BatchedJob;
use App\Models\Inventory\Inventory;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\RedisResponseCacheKey;
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

    public function handle(InventoryResponseCacheInterface $responseCache): void
    {
        Job::batch(function (BatchedJob $batch): void {
            $this->line(sprintf('working on batch <comment>%s</comment> ...', $batch->batch_id));

            $this->call('scout:import', ['model' => Inventory::class]);

            $this->line(sprintf('waiting for batch <comment>%s</comment> ...', $batch->batch_id));
        }, __CLASS__);

        // no matter if cache is disabled, invalidating the entire cache should be done
        $responseCache->forget([RedisResponseCacheKey::CLEAR_ALL_PATTERN]);

        $this->line(sprintf(
                'InvalidateCacheJob was dispatched using the pattern: <comment>%s</comment>',
                RedisResponseCacheKey::CLEAR_ALL_PATTERN
            )
        );
    }
}
