<?php

namespace App\Jobs\ElasticSearch\Cache;

use App\Jobs\Job;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;
use App\Services\ElasticSearch\Cache\UniqueCacheInvalidationInterface;

class InvalidateCacheJob extends Job
{
    /** @var string[] */
    private $keyPatterns;

    public $tries = 1;

    public $queue = 'inventory';

    public function tags(): array
    {
        return ['cache-invalidation'];
    }

    /**
     * @param array $keyPatterns
     */
    public function __construct(array $keyPatterns)
    {
        $this->keyPatterns = $keyPatterns;
    }

    public function handle(ResponseCacheInterface $service, UniqueCacheInvalidationInterface $uniqueCacheInvalidation): void
    {
        $service->invalidate(...$this->keyPatterns);
        $uniqueCacheInvalidation->removeJobsForKeys($this->keyPatterns);
    }

    public function failed(): void
    {
        app(UniqueCacheInvalidationInterface::class)->removeJobsForKeys($this->keyPatterns);
    }
}
