<?php


namespace App\Repositories\CRM\Invoice;


use App\Models\CRM\Account\Invoice;
use App\Utilities\JsonApi\WithRequestQueryable;
use App\Utilities\JsonApi\QueryBuilder;
use Dingo\Api\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class InvoiceRepository implements InvoiceRepositoryInterface
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

    public function __construct()
    {
        // assign the initial model to the query builder
        $this->withQuery(Invoice::query());
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
