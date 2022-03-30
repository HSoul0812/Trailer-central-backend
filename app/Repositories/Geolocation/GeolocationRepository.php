<?php

namespace App\Repositories\Geolocation;

use App\Models\Geolocation\Geolocation;

class GeolocationRepository implements GeolocationRepositoryInterface
{
    public function get(array $params): Geolocation
    {
        return Geolocation::where($params)->firstOrFail();
    }
}
