<?php

namespace App\Repositories\User;

use App\Exceptions\NotImplementedException;
use App\Models\User\Location\Geolocation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GeoLocationRepository implements GeoLocationRepositoryInterface
{

    public function create($params)
    {
        throw new NotImplementedException();
    }

    public function update($params)
    {
        throw new NotImplementedException();
    }

    /**
     * @param array $params
     * @return Geolocation
     * @throws ModelNotFoundException
     */
    public function get($params)
    {
        return Geolocation::where($params)->firstOrFail();
    }

    public function delete($params)
    {
        throw new NotImplementedException();
    }

    public function getAll($params)
    {
        throw new NotImplementedException();
    }

    public function search(array $params): Collection
    {
        $query = Geolocation::query();
        foreach ($params as $key => $value) {
            $query->where($key, 'like', '%' . $value . '%');
        }

        return $query->get();
    }
}
