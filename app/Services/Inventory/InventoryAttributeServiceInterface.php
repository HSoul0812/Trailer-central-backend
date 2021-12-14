<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Inventory;

/**
 * Interface InventoryAttributeServiceInterface
 *
 * @package App\Services\Inventory
 */
interface InventoryAttributeServiceInterface
{
    /**
     * @param array $params
     *
     * @return Inventory
     */
    public function update(array $params): Inventory;
}
