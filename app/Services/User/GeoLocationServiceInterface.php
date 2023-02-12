<?php

namespace App\Services\User;

use Grimzy\LaravelMysqlSpatial\Types\Point;

interface GeoLocationServiceInterface
{
    public function geoPointFromZipCode(string $zipCode): ?Point;
}
