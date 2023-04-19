<?php

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\DeletedInventory;
use App\Models\Inventory\File;
use Illuminate\Support\Collection;

/**
 * Class DeletedInventoryRepository
 * @package App\Repositories\Inventory
 */
class DeletedInventoryRepository implements DeletedInventoryRepositoryInterface
{
    /**
     * @param $params
     * @return mixed
     */
    public function create($params)
    {
        return DeletedInventory::create(
            [
                'vin'       => $params['vin'],
                'dealer_id' => $params['dealer_id']
            ]
        );
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
    public function get($params)
    {
        throw new NotImplementedException();
    }

    /**
     * @param $params
     * @return mixed
     */
    public function delete($params)
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
}
