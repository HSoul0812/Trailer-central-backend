<?php

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\InventoryMfg;

/**
 * Class InventoryRepository
 * @package App\Repositories\Inventory
 */
class ManufacturerRepository implements ManufacturerRepositoryInterface
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
     */
    public function getAll($params)
    {
        $query = InventoryMfg::where('id', '>', 0);

        if (isset($params['search_term'])) {
            $query = $query->where('label', 'LIKE', '%' . $params['search_term'] . '%');
        }
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

}
