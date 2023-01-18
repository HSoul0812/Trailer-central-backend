<?php

namespace App\Observers;

use App\Models\Inventory\InventoryImage;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;

class InventoryImageObserver
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
     * @param ResponseCacheKeyInterface $cacheKey
     * @param InventoryResponseCacheInterface $responseCache
     */
    public function __construct(ResponseCacheKeyInterface $cacheKey, InventoryResponseCacheInterface $responseCache)
    {
        $this->cacheKey = $cacheKey;
        $this->singleResponseCache = $responseCache->single();
        $this->searchResponseCache = $responseCache->search();
    }

    /**
     * Handle the image "created" event.
     *
     * @param InventoryImage $image
     * @return void
     */
    public function created(InventoryImage $image)
    {
        $this->deleted($image);
    }

    /**
     * Handle the image "updated" event.
     *
     * @param InventoryImage $image
     * @return void
     */
    public function updated(InventoryImage $image)
    {
        $this->deleted($image);
    }

    /**
     * Handle the image "deleted" event.
     *
     * @param InventoryImage $image
     * @return void
     */
    public function deleted(InventoryImage $image)
    {
        if (config('cache.inventory')) {
            $this->searchResponseCache->forget($this->cacheKey->deleteSingleFromCollection($image->inventory_id));
            $this->singleResponseCache->forget($this->cacheKey->deleteSingle($image->inventory_id));
        }
    }

    /**
     * Handle the image "restored" event.
     *
     * @param InventoryImage $image
     * @return void
     */
    public function restored(InventoryImage $image)
    {
        $this->deleted($image);
    }

    /**
     * Handle the image "force deleted" event.
     *
     * @param InventoryImage $image
     * @return void
     */
    public function forceDeleted(InventoryImage $image)
    {
        $this->deleted($image);
    }
}
