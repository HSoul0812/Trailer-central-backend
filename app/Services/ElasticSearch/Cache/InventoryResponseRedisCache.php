<?php

namespace App\Services\ElasticSearch\Cache;

use Illuminate\Support\Facades\Redis;

class InventoryResponseRedisCache implements InventoryResponseCacheInterface
{
    /**
     * @return ResponseCacheInterface
     */
    public function search(): ResponseCacheInterface
    {
        return new RedisResponseCache(Redis::connection('sdk-search-cache')->client(), app(UniqueCacheInvalidationInterface::class));
    }

    /**
     * @return ResponseCacheInterface
     */
    public function single(): ResponseCacheInterface
    {
        return new RedisResponseCache(Redis::connection('sdk-single-cache')->client(), app(UniqueCacheInvalidationInterface::class));
    }
}
