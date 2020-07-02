<?php


namespace App\Repositories\Pos;


use App\Exceptions\NotImplementedException;
use App\Models\Pos\Sale;
use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\WithRequestQueryable;
use App\Utilities\JsonApi\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SaleRepository extends RepositoryAbstract implements SaleRepositoryInterface
{
    use WithRequestQueryable;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    public function __construct()
    {
        // assign the initial model to the query builder
        $this->withQuery(Sale::query()); // todo may need to be injected here some other way
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
