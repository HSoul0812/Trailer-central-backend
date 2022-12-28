<?php

namespace App\Jobs\ElasticSearch\Cache;

use App\Jobs\Job;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;

class InvalidateCacheJob extends Job
{
    /** @var string[] */
    private $keyPatterns;

    public function __construct(array $keyPatterns)
    {
        $this->keyPatterns = $keyPatterns;
    }

    public function handle(ResponseCacheInterface $service): void
    {
        $service->invalidate(...$this->keyPatterns);
    }
}
