<?php

namespace App\Observers\Inventory;

use App\Models\Inventory\Inventory;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;

class InventoryObserver
{
    /** @var bool */
    private static $isCacheInvalidationEnabled = true;

    /** @var ResponseCacheKeyInterface */
    private $cacheKey;

    /**
     * @var InventoryResponseCacheInterface
     */
    private $responseCache;

    /**
     * @param ResponseCacheKeyInterface $cacheKey
     * @param InventoryResponseCacheInterface $responseCache
     */
    public function __construct(ResponseCacheKeyInterface $cacheKey, InventoryResponseCacheInterface $responseCache)
    {
        $this->cacheKey = $cacheKey;
        $this->responseCache = $responseCache;
    }

    public static function enableCacheInvalidation(): void
    {
        self::$isCacheInvalidationEnabled = true;
    }

    public static function disableCacheInvalidation():void
    {
        self::$isCacheInvalidationEnabled = false;
    }

    /**
     * Determine if cache invalidation is enabled
     */
    public static function isCacheInvalidationEnabled():bool
    {
        return self::$isCacheInvalidationEnabled;
    }

    /**
     * Handle the inventory "created" event.
     *
     * @param Inventory $inventory
     * @return void
     */
    public function created(Inventory $inventory)
    {
        if (self::$isCacheInvalidationEnabled) {

        }
    }

    /**
     * Handle the inventory "updated" event.
     *
     * @param Inventory $inventory
     * @return void
     */
    public function updated(Inventory $inventory)
    {
        if (self::$isCacheInvalidationEnabled) {

        }
    }

    /**
     * Handle the inventory "deleted" event.
     *
     * @param Inventory $inventory
     * @return void
     */
    public function deleted(Inventory $inventory)
    {
        if (self::$isCacheInvalidationEnabled) {

        }
    }

    /**
     * Handle the inventory "restored" event.
     *
     * @param Inventory $inventory
     * @return void
     */
    public function restored(Inventory $inventory)
    {
        if (self::$isCacheInvalidationEnabled) {

        }
    }

    /**
     * Handle the inventory "force deleted" event.
     *
     * @param Inventory $inventory
     * @return void
     */
    public function forceDeleted(Inventory $inventory)
    {
        $this->deleted($inventory);
    }
}
