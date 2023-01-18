<?php

namespace App\Services\ElasticSearch\Cache;

interface InventoryResponseCacheInterface
{
    /**
     * @return ResponseCacheInterface
     */
    public function search(): ResponseCacheInterface;

    /**
     * @return ResponseCacheInterface
     */
    public function single(): ResponseCacheInterface;
}
