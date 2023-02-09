<?php

namespace App\Jobs\ElasticSearch\Cache;

use App\Jobs\Job;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;

class InvalidateCacheJob extends Job
{
    private const TAG = 'cache-invalidation';

    /** @var string[] */
    private $keyPatterns;

    public $tries = 1;

    public $queue = 'inventory-cache';

    public function tags(): array
    {
        return array_merge(
            [self::TAG],
            $this->keyPatterns
        );
    }

    /**
     * @param array $keyPatterns
     */
    public function __construct(array $keyPatterns)
    {
        $this->keyPatterns = $keyPatterns;
    }

    public function handle(InventoryResponseCacheInterface $service): void
    {
        $service->invalidate($this->keyPatterns);
    }
}
