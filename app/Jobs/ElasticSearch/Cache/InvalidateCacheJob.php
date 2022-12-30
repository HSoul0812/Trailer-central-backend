<?php

namespace App\Jobs\ElasticSearch\Cache;

use App\Jobs\Job;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;
use App\Services\ElasticSearch\Cache\UniqueCacheInvalidationInterface;

class InvalidateCacheJob extends Job
{
    /** @var string[] */
    private $keyPatterns;

    /**
     * @param array $keyPatterns
     */
    public function __construct(array $keyPatterns)
    {
        $this->keyPatterns = $keyPatterns;
    }

    public function handle(ResponseCacheInterface $service, UniqueCacheInvalidationInterface $uniqueCacheInvalidation): void
    {
        $uniqueCacheInvalidation->createJobsForKeys($this->keyPatterns);
        $service->invalidate(...$this->keyPatterns);
        $uniqueCacheInvalidation->removeJobsForKeys($this->keyPatterns);
    }
    
    /**
     * @return void
     */
    public function failed()
    {
        app(UniqueCacheInvalidationInterface::class)->removeJobsForKeys($this->keyPatterns);
    }
}
