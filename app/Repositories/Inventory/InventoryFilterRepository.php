<?php

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\InventoryFilter;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class InventoryFilterRepository
 * @package App\Repositories\Inventory
 */
class InventoryFilterRepository implements InventoryFilterRepositoryInterface
{
    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function create($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function update($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function get($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function delete($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @return Collection<InventoryFilter>
     */
    public function getAll($params = [])
    {
        $query = InventoryFilter::query();
        if (isset($params['with'])) {
            $query->with($params['with']);
        }
        if (isset($params['order'])) {
            $query->orderBy($params['order']);
        }
        return $query->get();
    }
}
