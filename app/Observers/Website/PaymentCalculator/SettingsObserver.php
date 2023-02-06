<?php

namespace App\Observers\Website\PaymentCalculator;

use App\Jobs\Website\ReIndexInventoriesByDealersJob;
use App\Models\Inventory\Inventory;
use App\Models\Website\PaymentCalculator\Settings;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;

class SettingsObserver
{
    /**
     * @var ResponseCacheKeyInterface
     */
    private $cacheKey;

    /**
     * @var InventoryResponseCacheInterface
     */
    private $responseCache;

    public function __construct(ResponseCacheKeyInterface $cacheKey, InventoryResponseCacheInterface $responseCache)
    {
        $this->cacheKey = $cacheKey;
        $this->responseCache = $responseCache;
    }

    /**
     * Handle the settings "created" event.
     *
     * @param Settings $settings
     * @return void
     */
    public function created(Settings $settings)
    {
        $this->deleted($settings);
    }

    /**
     * Handle the settings "updated" event.
     *
     * @param Settings $settings
     * @return void
     */
    public function updated(Settings $settings)
    {
        $this->deleted($settings);
    }

    /**
     * Handle the settings "deleted" event.
     *
     * @param Settings $settings
     * @return void
     */
    public function deleted(Settings $settings)
    {
        $settings->load('website');

        if (($dealerId = $settings->website->dealer_id)) {
            dispatch(new ReIndexInventoriesByDealersJob([$dealerId]));
        }

        if (Inventory::isCacheInvalidationEnabled()) {
            $website = $settings->website;

            $this->responseCache->forget([
                $this->cacheKey->deleteByDealer($website->dealer_id),
                $this->cacheKey->deleteSingleByDealer($website->dealer_id)
            ]);
        }
    }

    /**
     * Handle the settings "restored" event.
     *
     * @param Settings $settings
     * @return void
     */
    public function restored(Settings $settings)
    {
        $this->deleted($settings);
    }

    /**
     * Handle the settings "force deleted" event.
     *
     * @param Settings $settings
     * @return void
     */
    public function forceDeleted(Settings $settings)
    {
        $this->deleted($settings);
    }
}
