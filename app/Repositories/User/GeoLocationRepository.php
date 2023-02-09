<?php
namespace App\Repositories\User;

use App\Exceptions\NotImplementedException;
use App\Models\User\Location\Geolocation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GeoLocationRepository implements GeoLocationRepositoryInterface {

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
        return Geolocation::where('zip', $params['zip'])->firstOrFail();
    }

    public function delete($params)
    {
        throw new NotImplementedException();
    }

    public function getAll($params)
    {
        throw new NotImplementedException();
    }
}
