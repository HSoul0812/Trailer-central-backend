<?php

namespace App\Services\Inventory;

use Exception;

/**
 * interface InventoryBulkUpdateManufacturerServiceInterface
 *
 * @package App\Services\Inventory
 */
interface InventoryBulkUpdateManufacturerServiceInterface
{

    /**
     * Updates Inventory Manufacturers
     *
     * @param array $params
     * @throws Exception
     */
    public function update(array $params);

    /**
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function bulkUpdateManufacturer(array $params);
}
