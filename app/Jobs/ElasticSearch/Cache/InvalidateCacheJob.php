<?php

namespace App\Jobs\ElasticSearch\Cache;

use App\Jobs\Job;
use App\Services\ElasticSearch\Cache\RedisResponseCacheKey;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;
use App\Services\ElasticSearch\Cache\UniqueCacheInvalidationInterface;

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
            $this->tagger($this->keyPatterns, function (string $pattern): string {
                return sprintf('%s:%s', self::TAG, $pattern);
            }),
            $this->tagger($this->keyPatterns, function (string $pattern): string {
                return sprintf('%s:%s', self::TAG, RedisResponseCacheKey::humanReadable($pattern));
            })
        );
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

    private function tagger(array $patterns, callable $apply): array
    {
        return array_map(static function (string $pattern) use ($apply) {
            return $apply($pattern);
        }, $patterns);
    }
}
