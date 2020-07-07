<?php


namespace App\Repositories\CRM\Payment;


use App\Models\CRM\Account\Payment;
use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\WithRequestQueryable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository extends RepositoryAbstract implements PaymentRepositoryInterface
{
    use WithRequestQueryable;

    public function __construct()
    {
        // assign the initial model to the query builder
        $this->withQuery(Payment::query()); // todo may need to be injected here some other way
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

}
