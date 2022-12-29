<?php

namespace App\Observers;

use App\Models\Inventory\Inventory;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;

class InventoryObserver
{
    /**
     * @var ResponseCacheKeyInterface
     */
    private $cacheKey;

    /**
     * @var ResponseCacheInterface
     */
    private $responseCache;

    /**
     * @param ResponseCacheKeyInterface $cacheKey
     * @param ResponseCacheInterface $responseCache
     */
    public function __construct(ResponseCacheKeyInterface $cacheKey, ResponseCacheInterface $responseCache)
    {
        $this->cacheKey = $cacheKey;
        $this->responseCache = $responseCache;
    }

    /**
     * Handle the inventory "created" event.
     *
     * @param Inventory $inventory
     * @return void
     */
    public function created(Inventory $inventory)
    {
        $this->responseCache->forget($this->cacheKey->deleteByDealer($inventory->dealer_id));
    }

    /**
     * Handle the inventory "updated" event.
     *
     * @param Inventory $inventory
     * @return void
     */
    public function updated(Inventory $inventory)
    {
        $this->responseCache->forget(
            $this->cacheKey->deleteByDealer($inventory->dealer_id),
            $this->cacheKey->deleteSingle($inventory->inventory_id)
        );
    }

    /**
     * Handle the inventory "deleted" event.
     *
     * @param Inventory $inventory
     * @return void
     */
    public function deleted(Inventory $inventory)
    {
        $this->responseCache->forget(
            $this->cacheKey->deleteSingleFromCollection($inventory->inventory_id),
            $this->cacheKey->deleteSingle($inventory->inventory_id)
        );
    }

    /**
     * Handle the inventory "restored" event.
     *
     * @param Inventory $inventory
     * @return void
     */
    public function restored(Inventory $inventory)
    {
        $this->responseCache->forget($this->cacheKey->deleteByDealer($inventory->dealer_id));
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
