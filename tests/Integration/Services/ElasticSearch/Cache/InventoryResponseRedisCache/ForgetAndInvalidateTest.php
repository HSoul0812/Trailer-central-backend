<?php

namespace Tests\Integration\Services\ElasticSearch\Cache\InventoryResponseRedisCache;

use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseRedisCache;
use App\Services\ElasticSearch\Cache\RedisResponseCacheKey;
use Mockery;

/**
 * @group DW
 * @group DW_ELASTICSEARCH
 * @group DW_INVENTORY
 *
 * @covers \App\Services\ElasticSearch\Cache\InventoryResponseRedisCache::invalidate
 * @covers \App\Services\ElasticSearch\Cache\InventoryResponseRedisCache::forget
 * @covers \App\Services\ElasticSearch\Cache\InventoryResponseRedisCache::sliceKeyPatterns because invalidate and forget rely on this
 */
class ForgetAndInvalidateTest extends AbstractInventoryResponseRedisCacheTest
{
    /**
     * @param  string  $method
     * @return void
     * @dataProvider methodsDataProvider
     */
    public function test_it_uses_the_correct_cache_connection_when_invalidating_both_kind_of_keys(string $method): void
    {
        $commonPatterns = [
            RedisResponseCacheKey::CLEAR_ALL_PATTERN
        ];

        $singlePatterns = [
            $this->cacheKey->deleteSingle(1234, 345),
            $this->cacheKey->deleteSingleByDealer(5678)
        ];

        $searchPatterns = [
            $this->cacheKey->deleteByDealer(5678),
            $this->cacheKey->deleteSingleFromCollection(1234)
        ];

        $patterns = array_merge($commonPatterns, $singlePatterns, $searchPatterns);

        /** @var Mockery\MockInterface|Mockery\Mock|InventoryResponseCacheInterface $inventoryCache */
        $inventoryCache = app(InventoryResponseCacheInterface::class);

        $this->singleResponseCache->expects($this->once())->method($method)->with(...array_merge($commonPatterns, $singlePatterns));
        $this->searchResponseCache->expects($this->once())->method($method)->with(...array_merge($commonPatterns, $searchPatterns));

        $inventoryCache->{$method}($patterns);
    }

    public function methodsDataProvider(): array
    {
        return [
            'calls `invalidate`' => ['invalidate'],
            'calls `forget`' => ['forget'],
        ];
    }
}
