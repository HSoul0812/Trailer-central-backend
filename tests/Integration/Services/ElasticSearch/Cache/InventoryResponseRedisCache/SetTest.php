<?php

namespace Tests\Integration\Services\ElasticSearch\Cache\InventoryResponseRedisCache;

use App\Http\Clients\ElasticSearch\ElasticSearchQueryResult;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseRedisCache;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;

/**
 * @group DW
 * @group DW_ELASTICSEARCH
 * @group DW_INVENTORY
 *
 * @covers \App\Services\ElasticSearch\Cache\InventoryResponseRedisCache::set
 */
class SetTest extends AbstractInventoryResponseRedisCacheTest
{
    use WithFaker;

    public function test_it_uses_the_single_cache_connection_when_setting_up(): void
    {
        $key = $this->cacheKey->single(1234, 345);
        $value = json_encode(['some' => 'value']);

        /** @var Mockery\MockInterface|Mockery\Mock|InventoryResponseCacheInterface $inventoryCache */
        $inventoryCache = app(InventoryResponseCacheInterface::class);

        $this->singleResponseCache->expects($this->once())->method('set')->with($key, $value);

        $inventoryCache->set($key, $value);
    }

    public function test_it_uses_the_search_cache_connection_when_setting_up(): void
    {
        $firstInventoryId = $this->faker->numberBetween(100, 299);
        $secondInventoryId = $this->faker->numberBetween(300, 599);

        $firstDealerId = $this->faker->numberBetween(100, 299);
        $secondDealerId = $this->faker->numberBetween(300, 599);

        $hits = [
            (object) ['_source' => (object) ['id' => $firstInventoryId, 'dealerId' => $firstDealerId]],
            (object) ['_source' => (object) ['id' => $secondInventoryId, 'dealerId' => $secondDealerId]],
        ];

        $requestId = $this->faker->sha1;
        $result = new ElasticSearchQueryResult([], [], 2, $hits);

        $key = $this->cacheKey->collection($requestId, $result);
        $value = json_encode(['some' => 'value']);

        /** @var Mockery\MockInterface|Mockery\Mock|InventoryResponseCacheInterface $inventoryCache */
        $inventoryCache = app(InventoryResponseCacheInterface::class);

        $this->searchResponseCache->expects($this->once())->method('set')->with($key, $value);

        $inventoryCache->set($key, $value);
    }
}
