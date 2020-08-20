<?php


namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\Status;

/**
 * Class StatusRepository
 * @package App\Repositories\Inventory
 */
class StatusRepository implements StatusRepositoryInterface
{
    public function create($params)
    {
        throw new NotImplementedException;
    }

    public function update($params)
    {
        throw new NotImplementedException;
    }

    public function get($params)
    {
        throw new NotImplementedException;
    }

    public function delete($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     * @return Status[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAll($params)
    {
        return Status::all();
    }
}
