<?php


namespace App\Repositories\CRM\Invoice;


use App\Models\CRM\Account\Invoice;
use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\QueryBuilder;
use App\Utilities\JsonApi\WithRequestQueryable;
use Dingo\Api\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class InvoiceRepository extends RepositoryAbstract implements InvoiceRepositoryInterface
{
    use WithRequestQueryable;

    /**
     * @var Request
     */
    private $request;
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
     * @return Collection|Invoice[]
     */
    public function get($params)
    {
        return $this->query()->get();
    }

    /**
     * find a single entity by primary key
     * @param int $id
     * @return Invoice|null
     */
    public function find($id)
    {
        return $this->query()->where('id', $id)->first();
    }

}
