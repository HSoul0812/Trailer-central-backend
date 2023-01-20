<?php

namespace App\Observers\Inventory;

use App\Models\Inventory\Inventory;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;

class InventoryObserver
{
    /** @var bool will help to determines when cache is enable thus jobs will be dispatched */
    private static $isCacheInvalidationEnabled = true;

    /** @var ResponseCacheKeyInterface */
    private $cacheKey;

    /**
     * @var ResponseCacheInterface
     */
    private $singleResponseCache;

    /**
     * @var ResponseCacheInterface
     */
    private $searchResponseCache;

    /**
     * @param ResponseCacheKeyInterface $cacheKey
     * @param InventoryResponseCacheInterface $responseCache
     */
    public function __construct(ResponseCacheKeyInterface $cacheKey, InventoryResponseCacheInterface $responseCache)
    {
        $this->cacheKey = $cacheKey;
        $this->singleResponseCache = $responseCache->single();
        $this->searchResponseCache = $responseCache->search();
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
            $this->searchResponseCache->forget($this->cacheKey->deleteByDealer($inventory->dealer_id));
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
            $this->searchResponseCache->forget($this->cacheKey->deleteByDealer($inventory->dealer_id));
            $this->singleResponseCache->forget($this->cacheKey->deleteSingle($inventory->inventory_id, $inventory->dealer_id));
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
            $this->searchResponseCache->forget($this->cacheKey->deleteSingleFromCollection($inventory->inventory_id));
            $this->singleResponseCache->forget($this->cacheKey->deleteSingle($inventory->inventory_id, $inventory->dealer_id));
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
            $this->searchResponseCache->forget($this->cacheKey->deleteByDealer($inventory->dealer_id));
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
