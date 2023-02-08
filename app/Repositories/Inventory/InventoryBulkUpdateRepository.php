<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\Inventory;
use Illuminate\Support\Collection;
use App\Exceptions\NotImplementedException;

/**
 * Class InventoryBulkUpdateRepository
 *
 * @package App\Repositories\Inventory
 */
class InventoryBulkUpdateRepository implements InventoryBulkUpdateRepositoryInterface
{
    protected $inventoryRepository;

    /**
     * Create a new controller instance.
     *
     * @param InventoryRepositoryInterface $inventoryRepository
     */
    public function __construct(InventoryRepositoryInterface $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

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
        return $this->inventoryRepository->get($params);
    }

    /**
     * {@inheritDoc}
     */
    public function bulkUpdateInventoryManufacturer($inventory, $params): Inventory
    {
        return $this->inventoryRepository->update($params);
    }
}
