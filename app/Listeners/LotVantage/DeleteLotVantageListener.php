<?php

namespace App\Listeners\LotVantage;

use App\Events\Inventory\InventoryDeleted;
use App\Services\LotVantage\LotVantageService;

/**
 * Class DeleteLotVantageListener
 * @package App\Listeners\LotVantage
 */
class DeleteLotVantageListener
{
    /**
     * @var LotVantageService
     */
    private $lotVantageService;

    /**
     * DeleteLotVantageListener constructor.
     * @param LotVantageService $lotVantageService
     */
    public function __construct(LotVantageService $lotVantageService)
    {
        $this->lotVantageService = $lotVantageService;
    }

    /**
     * Handle the event.
     *
     * @param  InventoryDeleted  $event
     * @return void
     */
    public function handle(InventoryDeleted $event)
    {
        $this->lotVantageService->deleteByInventory($event->inventory);
    }
}
