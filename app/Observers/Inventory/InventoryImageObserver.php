<?php

namespace App\Observers\Inventory;

use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryImage;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;

/**
 * @deprecated
 * @todo this must be analyzed given that inventory service/inventory model is already dispatching jobs becoming this redundant
 */
class InventoryImageObserver
{
    /**
     * @var ResponseCacheKeyInterface
     */
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

    /**
     * Handle the image "created" event.
     *
     * @param InventoryImage $image
     * @return void
     */
    public function created(InventoryImage $image)
    {
        // $this->deleted($image);
    }

    /**
     * Handle the image "updated" event.
     *
     * @param InventoryImage $image
     * @return void
     */
    public function updated(InventoryImage $image)
    {
        // $this->deleted($image);
    }

    /**
     * Handle the image "deleted" event.
     *
     * @param InventoryImage $image
     * @return void
     */
    public function deleted(InventoryImage $image)
    {
        if (Inventory::isCacheInvalidationEnabled()) {
            //  $this->responseCache->forget([
            //      $this->cacheKey->deleteSingleFromCollection($image->inventory_id),
            //      $this->cacheKey->deleteSingle($image->inventory_id, $image->inventory->dealer_id)
            //  ]);
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
