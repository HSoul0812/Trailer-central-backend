<?php

namespace App\Repositories\CRM\Refund;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\CRM\Dms\Refund;
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
        return $this->query()->get();
    }

    /**
     * @param array $params
     * @return Refund|null
     */
    public function get($params): ?Refund
    {
        if (!isset($params['id']) && !isset($params['filter']['id']['eq'])) {
            throw new RepositoryInvalidArgumentException('Refund id has been missed');
        }

        $query = $this->query();

        if (isset($params['id'])) {
            $query->where(Refund::getTableName() . '.id', '=', $params['id']);
        }

        if (isset($params['dealer_id'])) {
            $query->where(Refund::getTableName() . '.dealer_id', '=', $params['dealer_id']);
        }

        return $query->first();
    }
}
