<?php


namespace App\Repositories\Dms;


use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\WithRequestQueryable;
use Illuminate\Database\Eloquent\Builder;

class TaxCalculatorRepository extends RepositoryAbstract implements TaxCalculatorRepositoryInterface
{

    use WithRequestQueryable;

    public function __construct(Builder $baseQuery)
    {
        // assign the initial model to the query builder
        $this->withQuery($baseQuery);
    }

    public function get($params)
    {
        return $this->query()
            ->orWhereNull('dealer_id')
            ->get();
    }

    public function getAll($params)
    {
        return $this->get($params);
    }

}
