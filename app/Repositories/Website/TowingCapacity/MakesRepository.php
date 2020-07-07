<?php

namespace App\Repositories\Website\TowingCapacity;

use App\Exceptions\NotImplementedException;
use App\Models\Website\TowingCapacity\Make;
use Illuminate\Support\Facades\DB;

/**
 * Class MakesRepository
 * @package App\Repositories\Website\TowingCapacity
 */
class MakesRepository implements MakesRepositoryInterface
{
    /**
     * @param array $params
     * @throws NotImplementedException
     */
    public function getAll($params)
    {
        $query = Make::select('*');

        $query = $query->orderBy('name');

        return $query->get();
    }

    /**
     * @param string $year
     * @return mixed|void
     */
    public function getByYear(string $year)
    {
        $query = Make::select('*');

        $query = $query->whereHas('vehicles', function($q) use ($year) {
            $q->where('year', '=', $year);
        });

        $query = $query->orderBy('name');

        return $query->get();
    }

    /**
     * @return mixed
     */
    public function deleteAll()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $result = Make::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return $result;
    }

    /**
     * @param $params
     * @return mixed|void
     */
    public function create($params)
    {
        return Make::insert($params);
    }

    /**
     * @param array $params
     * @throws NotImplementedException
     */
    public function update($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     * @throws NotImplementedException
     */
    public function get($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     * @throws NotImplementedException
     */
    public function delete($params)
    {
        throw new NotImplementedException;
    }
}
