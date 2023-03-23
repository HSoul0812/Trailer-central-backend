<?php

namespace App\Repositories\Showroom;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Showroom\ShowroomFile;
use App\Repositories\RepositoryAbstract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ShowroomFileRepository
 * @package App\Repositories\Showroom
 */
class ShowroomFileRepository extends RepositoryAbstract implements ShowroomFileRepositoryInterface
{
    /**
     * @param $params
     * @return Collection
     */
    public function getAll($params): Collection
    {
        if (!isset($params['showroom_id'])) {
            throw new RepositoryInvalidArgumentException('showroom_id is absent', $params);
        }

        $query = ShowroomFile::query();
        $query->where('showroom_id', '=', $params['showroom_id']);

        return $query->get();
    }
}
