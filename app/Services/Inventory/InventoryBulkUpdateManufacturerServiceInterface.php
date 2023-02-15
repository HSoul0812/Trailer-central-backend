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
     * @throws Exception
     */
    public function update();

    /**
     * @param $params
     * @return mixed
     * @throws Exception
     */
    public function bulkUpdateManufacturer($params);
}
