<?php

namespace App\Services\User;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\Collection;

interface GeoLocationServiceInterface
{
    public function geoPointFromZipCode(string $zipCode): ?Point;

    public function search(array $params): Collection;
}
