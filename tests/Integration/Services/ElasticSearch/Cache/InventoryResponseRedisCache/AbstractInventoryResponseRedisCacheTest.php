<?php

namespace Tests\Integration\Services\ElasticSearch\Cache\InventoryResponseRedisCache;

use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseRedisCache;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Mockery;

abstract class AbstractInventoryResponseRedisCacheTest extends TestCase
{
    /** @var ResponseCacheKeyInterface */
    protected $cacheKey;

    /** @var MockObject|Mockery\LegacyMockInterface|Mockery\MockInterface|ResponseCacheInterface */
    protected $searchResponseCache;

    /** @var MockObject|Mockery\LegacyMockInterface|Mockery\MockInterface|ResponseCacheInterface */
    protected $singleResponseCache;

    public function setUp(): void
    {
        parent::setUp();

        $this->cacheKey = app(ResponseCacheKeyInterface::class);

        $this->singleResponseCache = $this->createStub(ResponseCacheInterface::class);
        $this->searchResponseCache = $this->createStub(ResponseCacheInterface::class);

        $this->inventoryCache = Mockery::mock(
            InventoryResponseRedisCache::class,
            [$this->cacheKey, $this->searchResponseCache, $this->singleResponseCache]
        )->makePartial();

        $this->instance(InventoryResponseCacheInterface::class, $this->inventoryCache);
    }
}
