<?php

namespace App\Observers\Website\PaymentCalculator;

use App\Models\Website\PaymentCalculator\Settings;
use App\Services\Inventory\InventoryServiceInterface;

class SettingsObserver
{
    /**
     * @var InventoryServiceInterface
     */
    private $inventoryService;

    public function __construct(InventoryServiceInterface $inventoryService)
    {
        $this->inventoryService = $inventoryService;
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
            $this->inventoryService->invalidateCacheAndReindexByDealerIds([$dealerId]);
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
