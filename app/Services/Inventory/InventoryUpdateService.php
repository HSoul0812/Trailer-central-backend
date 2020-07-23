<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Inventory;
use App\Repositories\Inventory\InventoryUpdateRepositoryInterface;

/**
 * Class InventoryUpdateService
 * @package App\Services\Inventory
 */
class InventoryUpdateService
{
    /**
     * @var InventoryUpdateRepositoryInterface
     */
    private $inventoryUpdateRepository;

    /**
     * InventoryUpdateService constructor.
     * @param InventoryUpdateRepositoryInterface $inventoryUpdateRepository
     */
    public function __construct(InventoryUpdateRepositoryInterface $inventoryUpdateRepository)
    {
        $this->inventoryUpdateRepository = $inventoryUpdateRepository;
    }

    /**
     * @param Inventory $inventory
     * @param string $action
     */
    public function insertOrUpdate(Inventory $inventory, string $action)
    {
        $params = [
            'inventory_id'   => $inventory->inventory_id,
            'dealer_id'      => $inventory->dealer_id,
            'stock'          => $inventory->stock,
            'location_id'    => $inventory->dealer_location_id,
            'action'         => $action,
            'specific_action'=> $action,
            'time_entered'   => time(),
            'processed'      => 0,
        ];

        return $this->inventoryUpdateRepository->insertOrUpdate($params);
    }
}
