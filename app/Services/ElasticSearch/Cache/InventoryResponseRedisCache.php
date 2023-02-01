<?php

namespace App\Services\ElasticSearch\Cache;

use App\Repositories\FeatureFlagRepositoryInterface;
use Illuminate\Support\Facades\Redis;

class InventoryResponseRedisCache implements InventoryResponseCacheInterface
{
    /**
     * @return ResponseCacheInterface
     */
    public function search(): ResponseCacheInterface
    {
        return new RedisResponseCache(
            Redis::connection('sdk-search-cache')->client(),
            app(FeatureFlagRepositoryInterface::class)
        );
    }

    /**
     * @return ResponseCacheInterface
     */
    public function single(): ResponseCacheInterface
    {
        return new RedisResponseCache(
            Redis::connection('sdk-single-cache')->client(),
            app(FeatureFlagRepositoryInterface::class)
        );
    }
}
