<?php

namespace App\Repositories\SysConfig;

use Illuminate\Database\Eloquent\Collection;

interface SysConfigRepositoryInterface
{
    public function getAll(array $params): Collection;
}
