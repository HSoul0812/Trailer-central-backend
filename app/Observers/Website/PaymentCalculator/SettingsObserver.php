<?php

namespace App\Observers\Website\PaymentCalculator;

use App\Jobs\Website\ReIndexInventoriesByDealersJob;
use App\Models\Website\PaymentCalculator\Settings;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;

class SettingsObserver
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

        if (config('cache.inventory')) {
            $website = $settings->website;
            $this->searchResponseCache->forget($this->cacheKey->deleteByDealer($website->dealer_id));
            $this->singleResponseCache->forget($this->cacheKey->deleteSingleByDealer($website->dealer_id));
        }

        if ($dealerId = $settings->website->dealer_id) {
            dispatch(new ReIndexInventoriesByDealersJob([$dealerId]));
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
