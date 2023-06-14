<?php

namespace App\Repositories\User;

use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;

interface GeoLocationRepositoryInterface extends Repository
{
    public function search(array $params): Collection;
}
