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

    /**
     * @return mixed
     */
    public function getYears()
    {
        return Vehicle::select('year')
            ->distinct()
            ->orderBy('year', 'DESC')
            ->get();
    }

    /**
     * @param int $year
     * @param string $makeId
     * @return mixed
     */
    public function getModels(int $year, int $makeId)
    {
        return Vehicle::select('model')
            ->distinct()
            ->where('year', $year)
            ->where('make_id', $makeId)
            ->orderBy('model')
            ->get();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function getAll($params)
    {
        $query = Vehicle::select('*');

        if (isset($params['year'])) {
            $query = $query->where('year', $params['year']);
        }

        if (isset($params['makeId'])) {
            $query = $query->where('make_id', $params['makeId']);
        }

        if (isset($params['model'])) {
            $query = $query->where('model', $params['model']);
        }

        return $query->get();
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
