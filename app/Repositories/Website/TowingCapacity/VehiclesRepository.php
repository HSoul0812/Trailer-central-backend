<?php

namespace App\Repositories\Website\TowingCapacity;

use App\Exceptions\NotImplementedException;
use App\Models\Website\TowingCapacity\Vehicle;
use Illuminate\Support\Facades\DB;

/**
 * Class VehiclesRepository
 * @package App\Repositories\Website\TowingCapacity
 */
class VehiclesRepository implements VehiclesRepositoryInterface
{

    /**
     * @param array $params
     * @return mixed
     */
    public function create($params)
    {
        return Vehicle::insert($params);
    }

    public function getAll($params)
    {
        // TODO: Implement getAll() method.
    }

    /**
     * @return mixed
     */
    public function deleteAll()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $result = Vehicle::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return $result;
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
}
