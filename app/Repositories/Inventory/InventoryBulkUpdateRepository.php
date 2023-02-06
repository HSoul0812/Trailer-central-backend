<?php

namespace App\Repositories\Inventory;

use Illuminate\Support\Collection;
use App\Models\Inventory\Inventory;
use App\Exceptions\NotImplementedException;
use App\Jobs\Inventory\InventoryBulkUpdateManufacturer;

/**
 * Class InventoryBulkUpdateRepository
 *
 * @package App\Repositories\Inventory
 */
class InventoryBulkUpdateRepository implements InventoryBulkUpdateRepositoryInterface
{
    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function get($params)
    {
        throw new NotImplementedException();
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function getAll($params)
    {
        throw new NotImplementedException();
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function create($params)
    {
        throw new NotImplementedException();
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function update($params): bool
    {
        throw new NotImplementedException();
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function delete($params): bool
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     */
    public function getInventoriesFromManufacturer($params): Collection
    {
        return Inventory::where($params)->get();
    }

    /**
     * {@inheritDoc}
     */
    public function bulkUpdateInventoryManufacturer($inventory, $params): bool
    {
        return $inventory->update($params);
    }

    /**
     * {@inheritDoc}
     */
    public function bulkUpdateManufacturer($params)
    {
        return dispatch((new InventoryBulkUpdateManufacturer($params))->onQueue('inventory'));
    }
}
