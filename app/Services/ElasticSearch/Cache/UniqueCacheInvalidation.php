<?php

namespace App\Services\ElasticSearch\Cache;

use \Redis as PhpRedis;

class UniqueCacheInvalidation implements UniqueCacheInvalidationInterface
{
    /**
     * @var PhpRedis
     */
    private $client;

    /** @var string */
    private const PREFIX = 'invalidation.job.';

    public const TTL = 21600; //6 hours

    /**
     * @param PhpRedis $client
     */
    public function __construct(PhpRedis $client)
    {
        $this->client = $client;
    }

    /**
     * @param array $keyPatterns
     * @return array
     */
    public function keysWithNoJobs(array $keyPatterns): array
    {
        return array_filter($keyPatterns, function ($key) {
            return !$this->jobExists($key);
        });
    }

    /**
     * @param string $pattern
     * @return bool
     */
    public function jobExists(string $pattern): bool
    {
        $key = sprintf('*%s%s', self::PREFIX, $this->sanitizePattern($pattern));
        return count($this->client->keys($key)) > 0;
    }

    /**
     * @param array $keyPatterns
     * @return void
     */
    public function createJobsForKeys(array $keyPatterns): void
    {
        foreach ($keyPatterns as $keyPattern) {
            $key = sprintf('%s%s', self::PREFIX, $this->sanitizePattern($keyPattern));
            $this->client->set($key, 1, self::TTL);
        }
    }

    /**
     * @param array $keyPatterns
     * @return void
     */
    public function removeJobsForKeys(array $keyPatterns): void
    {
        foreach ($keyPatterns as $keyPattern) {
            $key = sprintf('%s%s', self::PREFIX, $this->sanitizePattern($keyPattern));
            $this->client->unlink($key);
        }
    }

    /**
     * @param string $pattern
     * @return string
     */
    private function sanitizePattern(string $pattern): string
    {
        return str_replace('*', '_', $pattern);
    }
}
