<?php

namespace Tests\Integration\Jobs\ElasticSearch\Cache;

use App\Jobs\ElasticSearch\Cache\InvalidateCacheJob;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseRedisCache;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;
use App\Services\ElasticSearch\Cache\UniqueCacheInvalidationInterface;
use Mockery;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * @group ElasticSearch
 * @group Inventory
 */
class InvalidateCacheJobTest extends TestCase
{
    /** @var ResponseCacheKeyInterface */
    private $cacheKey;

    /** @var MockObject */
    private $searchResponseCache;

    /** @var MockObject */
    private $singleResponseCache;

    /** @var MockObject */
    private $inventoryCache;

    /** @var */
    private $uniqueCacheInvalidation;

    public function setUp(): void
    {
        parent::setUp();
        $this->cacheKey = app(ResponseCacheKeyInterface::class);
        $this->singleResponseCache = Mockery::mock(ResponseCacheInterface::class);
        $this->searchResponseCache = Mockery::mock(ResponseCacheInterface::class);
        $this->uniqueCacheInvalidation = $this->createStub(UniqueCacheInvalidationInterface::class);
        $this->inventoryCache = $this->createStub(InventoryResponseRedisCache::class);
        $this->inventoryCache->method('search')
            ->willReturn($this->searchResponseCache);
        $this->inventoryCache->method('single')
            ->willReturn($this->singleResponseCache);

        $this->instance(InventoryResponseCacheInterface::class, $this->inventoryCache);
    }

    public function test_it_uses_the_correct_cache_connection_when_invalidating_single_keys()
    {
        $patterns = [
            $this->cacheKey->deleteSingle(1234),
            $this->cacheKey->deleteSingleByDealer(5678)
        ];

        $job = new InvalidateCacheJob($patterns);

        $this->inventoryCache->expects($this->once())->method('single');
        $this->singleResponseCache->shouldReceive('invalidate')->withArgs($patterns)->once();
        $this->inventoryCache->expects($this->never())->method('search');

        $job->handle($this->inventoryCache, $this->uniqueCacheInvalidation, $this->cacheKey);
    }

    public function test_it_uses_the_correct_cache_connection_when_invalidating_search_keys()
    {
        $patterns = [
            $this->cacheKey->deleteSingleFromCollection(1234),
            $this->cacheKey->deleteByDealer(5678)
        ];

        $job = new InvalidateCacheJob($patterns);

        $this->inventoryCache->expects($this->once())->method('search');
        $this->searchResponseCache->shouldReceive('invalidate')->withArgs($patterns)->once();
        $this->inventoryCache->expects($this->never())->method('single');

        $job->handle($this->inventoryCache, $this->uniqueCacheInvalidation, $this->cacheKey);
    }

    public function test_it_uses_the_correct_cache_connections_when_invalidating_mixed_keys()
    {
        $searchKeys = [
            $this->cacheKey->deleteSingleFromCollection(1234),
            $this->cacheKey->deleteByDealer(5678)
        ];

        $singleKeys = [
            $this->cacheKey->deleteSingle(9999),
            $this->cacheKey->deleteSingleByDealer(8982)
        ];

        $patterns = array_merge($searchKeys, $singleKeys);

        $job = new InvalidateCacheJob($patterns);

        $this->inventoryCache->expects($this->once())->method('single');
        $this->singleResponseCache->shouldReceive('invalidate')->withArgs($singleKeys)->once();

        $this->inventoryCache->expects($this->once())->method('search');
        $this->searchResponseCache->shouldReceive('invalidate')->withArgs($searchKeys)->once();

        $job->handle($this->inventoryCache, $this->uniqueCacheInvalidation, $this->cacheKey);
    }

    public function test_it_removes_lock_for_keys_after_invalidation()
    {
        $patterns = [
            $this->cacheKey->deleteSingle(1234),
            $this->cacheKey->deleteSingleByDealer(5678)
        ];

        $job = new InvalidateCacheJob($patterns);
        $uniqueCacheInvalidation = Mockery::mock(UniqueCacheInvalidationInterface::class);

        $this->inventoryCache->expects($this->once())->method('single');
        $this->singleResponseCache->shouldReceive('invalidate')->withArgs($patterns)->once();
        $uniqueCacheInvalidation->shouldReceive('removeJobsForKeys')->with($patterns)->once();

        $job->handle($this->inventoryCache, $uniqueCacheInvalidation, $this->cacheKey);
    }
}
