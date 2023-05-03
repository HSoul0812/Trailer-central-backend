<?php

namespace App\Repositories\SysConfig;

use App\Models\SysConfig\SysConfig;
use Illuminate\Database\Eloquent\Collection;

class SysConfigRepository implements SysConfigRepositoryInterface
{
    public function getAll(array $params): Collection
    {
        $query = SysConfig::query();
        if (isset($params['key'])) {
            $query->where('key', 'LIKE', $params['key'] . '%');
        }

        return $query->get();
    }
}
