<?php

namespace App\Observers;

use App\Models\Inventory\Inventory;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\Inventory\InventoryUpdateSourceInterface;
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
    private $singleResponseCache;

    /**
     * @var ResponseCacheInterface
     */
    private $searchResponseCache;

    /**
     * @var InventoryUpdateSourceInterface
     */
    /**
     * @var InventoryUpdateSourceInterface
     */
    private $updateSource;

    /**
     * @param ResponseCacheKeyInterface $cacheKey
     * @param InventoryResponseCacheInterface $responseCache
     * @param InventoryUpdateSourceInterface $updateSource
     */
    public function __construct(ResponseCacheKeyInterface $cacheKey, InventoryResponseCacheInterface $responseCache, InventoryUpdateSourceInterface $updateSource)
    {
        $this->cacheKey = $cacheKey;
        $this->updateSource = $updateSource;
        $this->singleResponseCache = $responseCache->single();
        $this->searchResponseCache = $responseCache->search();
    }

    /**
     * Handle the inventory "created" event.
     *
     * @param Inventory $inventory
     * @return void
     */
    public function created(Inventory $inventory)
    {
        if (config('cache.inventory') && !$this->updateSource->integrations()) {
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
        if (config('cache.inventory') && !$this->updateSource->integrations()) {
            $this->searchResponseCache->forget($this->cacheKey->deleteByDealer($inventory->dealer_id));
            $this->singleResponseCache->forget($this->cacheKey->deleteSingle($inventory->inventory_id));
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
        if (config('cache.inventory') && !$this->updateSource->integrations()) {
            $this->searchResponseCache->forget($this->cacheKey->deleteSingleFromCollection($inventory->inventory_id));
            $this->singleResponseCache->forget($this->cacheKey->deleteSingle($inventory->inventory_id));
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
        if (config('cache.inventory') && !$this->updateSource->integrations()) {
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
