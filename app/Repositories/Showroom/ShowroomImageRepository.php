<?php

namespace App\Repositories\Showroom;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Showroom\ShowroomImage;
use App\Repositories\RepositoryAbstract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ShowroomImageRepository
 * @package App\Repositories\Showroom
 */
class ShowroomImageRepository extends RepositoryAbstract implements ShowroomImageRepositoryInterface
{
    /**
     * @param $params
     * @return Collection
     */
    public function getAll($params): Collection
    {
        if (!isset($params['showroom_id'])) {
            throw new RepositoryInvalidArgumentException('showroom_id is absent');
        }

        $query = ShowroomImage::query();
        $query->where('showroom_id', '=', $params['showroom_id']);

        return $query->get();
    }
}
