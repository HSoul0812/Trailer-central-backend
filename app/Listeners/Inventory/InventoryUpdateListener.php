<?php

namespace App\Listeners\Inventory;

use App\Events\Inventory\InventoryEventInterface;
use App\Services\Inventory\InventoryUpdateService;

/**
 * Class InventoryUpdateListener
 * @package App\Listeners\Inventory
 */
class InventoryUpdateListener
{
    /**
     * @var InventoryUpdateService
     */
    private $inventoryUpdateService;

    /**
     * InventoryUpdateListener constructor.
     * @param InventoryUpdateService $inventoryUpdateService
     */
    public function __construct(InventoryUpdateService $inventoryUpdateService)
    {
        $this->inventoryUpdateService = $inventoryUpdateService;
    }

    /**
     * @param InventoryEventInterface $event
     */
    public function handle(InventoryEventInterface $event)
    {
        $this->inventoryUpdateService->insertOrUpdate($event->inventory, $event->getAction());
    }
}
