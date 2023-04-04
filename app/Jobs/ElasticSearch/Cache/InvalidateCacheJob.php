<?php

namespace App\Jobs\ElasticSearch\Cache;

use App\Jobs\Job;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;

class InvalidateCacheJob extends Job
{
    private const TAG = 'cache-invalidation';

    public const DELAY_ADDITION_IN_SECONDS = 2;

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

        // given we have a configurable ElasticSearch refresh interval in seconds,
        // we need to made sure this will be processed after that period
        $this->delay = ((int) config('elastic.scout_driver.settings.inventory.refresh_interval')) + self::DELAY_ADDITION_IN_SECONDS;
    }

    public function handle(InventoryResponseCacheInterface $service): void
    {
        $service->invalidate($this->keyPatterns);
    }
}
