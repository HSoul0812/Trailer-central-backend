<?php


namespace App\Repositories\CRM\Payment;


use App\Models\CRM\Account\Payment;
use App\Utilities\JsonApi\WithRequestQueryable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository implements PaymentRepositoryInterface
{
    use WithRequestQueryable;

    public function __construct()
    {
        // assign the initial model to the query builder
        $this->withQuery(Payment::query());
    }

    /**
     * find records; similar to findBy()
     * @param array $params
     * @return Collection|Payment[]
     */
    public function get($params)
    {
        return $this->query()->get();
    }

    /**
     * find a single entity by primary key
     * @param int $id
     * @return Builder|Payment|null
     */
    public function find($id)
    {
        return $this->query()->where('id', $id)->first();
    }

    /**
     * @inheritDoc
     */
    public function create($params)
    {
        // TODO: Implement create() method.
    }

    /**
     * @inheritDoc
     */
    public function update($params)
    {
        // TODO: Implement update() method.
    }


    /**
     * @inheritDoc
     */
    public function delete($params)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @inheritDoc
     */
    public function getAll($params)
    {
        // TODO: Implement getAll() method.
    }

}
