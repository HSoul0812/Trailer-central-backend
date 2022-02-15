<?php

namespace App\Repositories\Dms\ServiceOrder;

use App\Models\CRM\Dms\ServiceOrder\Type;
use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\WithRequestQueryable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TypeRepository
 * @package App\Repositories\Dms\ServiceOrder
 */
class TypeRepository extends RepositoryAbstract implements TypeRepositoryInterface
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
