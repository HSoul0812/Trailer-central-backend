<?php

namespace App\Console\Commands\Inventory\Jobs;

use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\RedisResponseCacheKey;
use Illuminate\Support\Facades\Redis;
use Illuminate\Console\Command;
use Redis as PhpRedis;

class FlushInventoryInvalidateCacheQueueJobs extends Command
{
    /** @var string The name and signature of the console command. */
    protected $signature = 'inventory:flush-inventory-cache-queue-jobs';

    /** @var string */
    protected $description = 'Will flush inventory cache queue jobs';

    public function handle(InventoryResponseCacheInterface $responseCache): void
    {
        /** @var PhpRedis $connection */
        $connection = Redis::connection();

        // so far, the only queue which is necessary to flush time to time is `inventory-cache` to avoid high CPU usages
        $connection->del('queues:inventory-cache');
        $connection->del('queues:inventory-cache:delayed');

        // no matter if cache is disabled, invalidating the entire cache should be done
        $responseCache->forget([RedisResponseCacheKey::CLEAR_ALL_PATTERN]);

        // due we've removed all invalidation jobs, so we should invalidate all at once
        $this->line(sprintf(
                'InvalidateCacheJob was dispatched using the pattern: <comment>%s</comment>',
                RedisResponseCacheKey::CLEAR_ALL_PATTERN
            )
        );
    }
}
