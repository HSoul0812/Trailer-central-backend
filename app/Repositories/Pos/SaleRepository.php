<?php


namespace App\Repositories\Pos;


use App\Models\Pos\Sale;
use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\QueryBuilder;
use App\Utilities\JsonApi\WithRequestQueryable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SaleRepository extends RepositoryAbstract implements SaleRepositoryInterface
{
    use WithRequestQueryable;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    public function __construct(Builder $baseQuery)
    {
        // assign the initial model to the query builder
        $this->withQuery($baseQuery);
    }

    /**
     * find records; similar to findBy()
     * @param array $params
     * @return Collection|Sale[]
     */
    public function get($params)
    {
        return $this->query()->get();
    }

    /**
     * find a single entity by primary key
     * @param int $id
     * @return Builder|Sale|null
     */
    public function find($id)
    {
        return $this->query()->where('id', $id)->first();
    }
}
