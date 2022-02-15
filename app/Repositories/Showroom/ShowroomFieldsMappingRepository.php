<?php

namespace App\Repositories\Showroom;

use App\Models\Showroom\ShowroomFieldsMapping;
use App\Repositories\RepositoryAbstract;
use Illuminate\Support\Collection;

/**
 * Class ShowroomFieldsMappingRepository
 * @package App\Repositories\Showroom
 */
class ShowroomFieldsMappingRepository extends RepositoryAbstract implements ShowroomFieldsMappingRepositoryInterface
{
    public function getAll($params): Collection
    {
        return ShowroomFieldsMapping::all();
    }
}
