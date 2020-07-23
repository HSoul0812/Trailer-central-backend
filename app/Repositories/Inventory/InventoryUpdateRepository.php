<?php


namespace App\Repositories\Inventory;

use App\Models\Inventory\InventoryUpdate;

/**
 * Class InventoryUpdateRepository
 * @package App\Repositories\Inventory
 */
class InventoryUpdateRepository implements InventoryUpdateRepositoryInterface
{
    /**
     * @param array $params
     */
    public function insertOrUpdate($params)
    {
        $inventoryUpdate = InventoryUpdate::firstOrNew(['inventory_id' => $params['inventory_id']]);

        return $inventoryUpdate->fill($params)->save();
    }
}
