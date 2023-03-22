<?php

namespace App\Repositories\Showroom;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Showroom\ShowroomGenericMap;
use App\Repositories\Repository;
use App\Repositories\RepositoryAbstract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ShowroomGenericMapRepository
 * @package App\Repositories\Showroom
 */
class ShowroomGenericMapRepository extends RepositoryAbstract implements ShowroomGenericMapRepositoryInterface
{
    /**
     * @param array $params
     * @return Collection
     */
    public function getAll($params): Collection
    {
        if (!isset($params['external_mfg_key'])) {
            throw new RepositoryInvalidArgumentException('external_mfg_key is absent', $params);
        }

        $query = ShowroomGenericMap::query();
        $query->where('external_mfg_key', '=', $params['external_mfg_key']);

        return $query->get();
    }
}
