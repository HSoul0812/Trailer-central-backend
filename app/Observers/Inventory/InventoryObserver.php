<?php

namespace App\Observers\Inventory;

use App\Models\Inventory\Inventory;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;

class InventoryObserver
{
    /** @var bool will help to determines when cache is enable thus jobs will be dispatched */
    private static $isCacheInvalidationEnabled = true;

    /** @var ResponseCacheKeyInterface */
    private $cacheKey;

    /** @var ResponseCacheInterface */
    private $responseCache;

    public function __construct(ResponseCacheKeyInterface $cacheKey, ResponseCacheInterface $responseCache)
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
            $this->responseCache->forget($this->cacheKey->deleteByDealer($inventory->dealer_id));
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
            $this->responseCache->forget(
                $this->cacheKey->deleteByDealer($inventory->dealer_id),
                $this->cacheKey->deleteSingle($inventory->inventory_id)
            );
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
            $this->responseCache->forget(
                $this->cacheKey->deleteSingleFromCollection($inventory->inventory_id),
                $this->cacheKey->deleteSingle($inventory->inventory_id)
            );
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
            $this->responseCache->forget($this->cacheKey->deleteByDealer($inventory->dealer_id));
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
