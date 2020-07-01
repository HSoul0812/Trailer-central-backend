<?php


namespace App\Repositories\Pos;


use App\Models\Pos\Sale;
use App\Utilities\JsonApi\WithRequestQueryable;
use App\Utilities\JsonApi\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SaleRepository implements SaleRepositoryInterface
{
    use WithRequestQueryable;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        // assign the initial model to the query builder
        $this->withQuery(Sale::query());
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
    public function create($params)
    {
        // TODO: Implement create() method.
    }

    public function update($params)
    {
        // TODO: Implement update() method.
    }

    public function delete($params)
    {
        // TODO: Implement delete() method.
    }

    public function getAll($params)
    {
        // TODO: Implement getAll() method.
    }

}
