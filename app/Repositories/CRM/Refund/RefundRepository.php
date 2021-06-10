<?php

namespace App\Repositories\CRM\Refund;

use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\WithRequestQueryable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class RefundRepository
 * @package App\Repositories\CRM\Payment
 */
class RefundRepository extends RepositoryAbstract implements RefundRepositoryInterface
{
    use WithRequestQueryable;

    public function __construct(Builder $baseQuery)
    {
        $this->withQuery($baseQuery);
    }

    /**
     * @param array $params
     * @return Collection
     */
    public function getAll($params): Collection
    {
        $query = $this->query();

        return $query->get();
    }
}
