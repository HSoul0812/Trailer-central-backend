<?php

namespace App\Observers\Inventory;

use App\Models\Inventory\Inventory;
use App\Repositories\Inventory\DeletedInventoryRepositoryInterface;
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
     * @var DeletedInventoryRepositoryInterface
     */
    private $deletedInventoryRepo;

    /**
     * @param ResponseCacheKeyInterface $cacheKey
     * @param InventoryResponseCacheInterface $responseCache
     * @param DeletedInventoryRepositoryInterface $deletedInventoryRepo
     */
    public function __construct(
        ResponseCacheKeyInterface $cacheKey,
        InventoryResponseCacheInterface $responseCache,
        DeletedInventoryRepositoryInterface $deletedInventoryRepo
    )
    {
        $this->cacheKey = $cacheKey;
        $this->responseCache = $responseCache;
        $this->deletedInventoryRepo = $deletedInventoryRepo;
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
            $this->responseCache->forget([$this->cacheKey->deleteByDealer($inventory->dealer_id)]);
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
            $this->responseCache->forget([
                    $this->cacheKey->deleteByDealer($inventory->dealer_id),
                    $this->cacheKey->deleteSingle($inventory->inventory_id, $inventory->dealer_id)
                ]
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
            $this->responseCache->forget([
                $this->cacheKey->deleteSingleFromCollection($inventory->inventory_id),
                $this->cacheKey->deleteSingle($inventory->inventory_id, $inventory->dealer_id)
            ]);
        }

        // Add record to deleted_inventory table
        $this->deletedInventoryRepo->create([
            'vin' => $inventory->vin,
            'dealer_id' => $inventory->dealer_id
        ]);
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
            $this->responseCache->forget([$this->cacheKey->deleteByDealer($inventory->dealer_id)]);
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
