<?php

namespace App\Observers\Website\PaymentCalculator;

use App\Jobs\Website\ReIndexInventoriesByDealersJob;
use App\Models\Website\PaymentCalculator\Settings;
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

            $this->responseCache->forget(
                $this->cacheKey->deleteByDealer($website->dealer_id),
                $this->cacheKey->deleteSingleByDealer($website->dealer_id)
            );
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
