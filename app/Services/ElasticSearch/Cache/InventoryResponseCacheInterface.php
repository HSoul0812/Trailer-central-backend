<?php

namespace App\Services\ElasticSearch\Cache;

interface InventoryResponseCacheInterface
{
    /**
     * It should store the cache in the proper database
     *
     * @param  string  $key
     * @param  string  $value
     * @return void
     */
    public function set(string $key, string $value): void;

    /**
     * It should queue the jobs to invalidate
     *
     * @param  array  $keyPatterns
     * @return void
     */
    public function forget(array $keyPatterns): void;

    /**
     * It should handle invalidation process
     *
     * @param  array  $keyPatterns
     * @return void
     */
    public function invalidate(array $keyPatterns): void;
}
