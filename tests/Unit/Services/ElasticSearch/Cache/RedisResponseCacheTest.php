<?php

namespace Tests\Unit\Services\ElasticSearch\Cache;

use App\Services\ElasticSearch\Cache\RedisResponseCache;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;
use App\Services\ElasticSearch\Cache\UniqueCacheInvalidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use \Redis as PhpRedis;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;

/**
 * @group DW
 * @group DW_ELASTICSEARCH
 * @group DW_INVENTORY
 *
 */
class RedisResponseCacheTest extends TestCase
{
    /** @var RedisResponseCache */
    private $responseCache;

    /** @var ResponseCacheKeyInterface */
    private $cacheKey;

    /** @var MockObject */
    private $phpRedis;

    public function setUp(): void
    {
        parent::setUp();

        $this->phpRedis = $this->createStub(PhpRedis::class);
        $this->phpRedis->method('scan')
            ->willReturn(['abcd']);
        $this->phpRedis->method('unlink')
            ->willReturn(1234);
        $this->responseCache = new RedisResponseCache($this->phpRedis, $this->createStub(UniqueCacheInvalidationInterface::class));
        $this->instance(ResponseCacheInterface::class, $this->responseCache);

        $this->cacheKey = app(ResponseCacheKeyInterface::class);
    }

    public function test_it_invalidates_with_key_if_a_normal_key_is_provided()
    {
        $this->phpRedis->expects($this->never())->method('scan');
        $this->phpRedis->expects($this->once())->method('unlink');

        $this->responseCache->invalidate($this->cacheKey->deleteSingle(1234, 5678));
    }

    public function test_it_invalidates_with_scan_if_a_wildcard_is_provided()
    {
        $this->phpRedis->expects($this->once())->method('scan');
        $this->phpRedis->expects($this->once())->method('unlink');

        $this->responseCache->invalidate($this->cacheKey->deleteSingleFromCollection(1234));
    }
}
