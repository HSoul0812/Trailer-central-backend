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
 * @group DW_JOBS
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
        $singlePatterns = [
            $this->cacheKey->deleteSingle(1234, 345),
            $this->cacheKey->deleteSingleByDealer(5678)
        ];

        $searchPatterns = [
            $this->cacheKey->deleteByDealer(5678),
            $this->cacheKey->deleteSingleFromCollection(1234)
        ];

        $patterns = array_merge($singlePatterns, $searchPatterns);

        /** @var Mockery\MockInterface|Mockery\Mock|InventoryResponseCacheInterface $inventoryCache */
        $inventoryCache = app(InventoryResponseCacheInterface::class);

        $this->singleResponseCache->allows($method)->withArgs($singlePatterns);
        $this->searchResponseCache->allows($method)->withArgs($searchPatterns);

        $this->expectNotToPerformAssertions();

        $inventoryCache->{$method}($patterns);
    }

    /**
     * @param  string  $method
     * @return void
     * @dataProvider methodsDataProvider
     */
   public function test_it_uses_the_correct_cache_connection_when_invalidating_both_kind_of_keys_at_same_time(string $method): void
    {
        $patterns = [
            RedisResponseCacheKey::CLEAR_ALL_PATTERN
        ];

        /** @var Mockery\MockInterface|Mockery\Mock|InventoryResponseCacheInterface $inventoryCache */
        $inventoryCache = app(InventoryResponseCacheInterface::class);

        $this->singleResponseCache->allows($method)->withArgs($patterns);
        $this->searchResponseCache->allows($method)->withArgs($patterns);

        $this->expectNotToPerformAssertions();

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
