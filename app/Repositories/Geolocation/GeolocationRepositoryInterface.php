<?php

namespace App\Repositories\Geolocation;

use App\Models\Geolocation\Geolocation;

interface GeolocationRepositoryInterface
{
    public function get(array $params): Geolocation;
}
