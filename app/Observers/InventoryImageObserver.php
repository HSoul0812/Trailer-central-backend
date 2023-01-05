<?php

namespace App\Observers;

use App\Models\Inventory\InventoryImage;
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
            $this->responseCache->forget(
                $this->cacheKey->deleteSingleFromCollection($image->inventory_id),
                $this->cacheKey->deleteSingle($image->inventory_id)
            );
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
