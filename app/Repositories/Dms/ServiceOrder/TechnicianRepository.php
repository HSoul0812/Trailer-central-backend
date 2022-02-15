<?php

namespace App\Repositories\Dms\ServiceOrder;

use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\WithRequestQueryable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TechnicianRepository
 * @package App\Repositories\Dms\ServiceOrder
 */
class TechnicianRepository extends RepositoryAbstract implements TechnicianRepositoryInterface
{
    use WithRequestQueryable;

    public function __construct(Builder $baseQuery)
    {
        $this->withQuery($baseQuery);
    }

    public function getAll($params)
    {
        return $this->query()->get();
    }
}
