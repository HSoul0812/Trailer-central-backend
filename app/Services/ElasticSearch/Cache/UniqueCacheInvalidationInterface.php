<?php

namespace App\Services\ElasticSearch\Cache;

interface UniqueCacheInvalidationInterface
{
    /**
     * @param array $keyPatterns
     * @return array
     */
    public function keysWithNoJobs(array $keyPatterns): array;

    /**
     * @param string $pattern
     * @return bool
     */
    public function jobExists(string $pattern): bool;

    /**
     * @param array $keyPatterns
     * @return void
     */
    public function createJobsForKeys(array $keyPatterns): void;

    /**
     * @param array $keyPatterns
     * @return void
     */
    public function removeJobsForKeys(array $keyPatterns): void;
}
