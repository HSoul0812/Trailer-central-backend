<?php

namespace App\Repositories\Inventory;

use Exception;
use App\Repositories\Repository;
use Illuminate\Support\Collection;
use App\Models\Inventory\Inventory;

/**
 * Interface InventoryBulkUpdateRepositoryInterface
 *
 * @package App\Repositories\Inventory
 */
interface InventoryBulkUpdateRepositoryInterface extends Repository
{
    /**
     * @param array $params
     * @return Collection
     */
    public function getInventoriesFromManufacturer(array $params): Collection;

    /**
     * @param Inventory $inventory
     * @param array $params
     * @return Inventory
     */
    public function bulkUpdateInventoryManufacturer(Inventory $inventory, array $params): Inventory;
}
