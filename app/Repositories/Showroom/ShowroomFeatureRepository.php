<?php

namespace App\Repositories\Showroom;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Showroom\ShowroomFeature;
use App\Repositories\RepositoryAbstract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ShowroomFeatureRepository
 * @package App\Repositories\Showroom
 */
class ShowroomFeatureRepository extends RepositoryAbstract implements ShowroomFeatureRepositoryInterface
{
    /**
     * @param $params
     * @return Collection
     */
    public function getAll($params): Collection
    {
        if (!isset($params['showroom_id'])) {
            throw new RepositoryInvalidArgumentException('Showroom id is absent');
        }

        $query = ShowroomFeature::query();
        $query = $query->where('showroom_id', '=', $params['showroom_id']);

        return $query->get();
    }
}
