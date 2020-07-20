<?php


namespace App\Repositories\Dms;


use App\Models\CRM\Dms\FinancingCompany;
use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\WithRequestQueryable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FinancingCompanyRepository extends RepositoryAbstract implements FinancingCompanyRepositoryInterface
{
    use WithRequestQueryable;

    public function __construct()
    {
        // assign the initial model to the query builder
        $this->withQuery(FinancingCompany::query()); // todo may need to be injected here some other way
    }

    /**
     * find records; similar to findBy()
     * @param array $params
     * @return Collection|FinancingCompany[]
     */
    public function get($params)
    {
        return $this->query()->get();
    }

    /**
     * find a single entity by primary key
     * @param int $id
     * @return Builder|FinancingCompany|null
     */
    public function find($id)
    {
        return $this->query()->where('id', $id)->first();
    }

    /**
     * @param $params
     * @return FinancingCompany|null
     * @throws \Throwable
     */
    public function create($params)
    {
        $financingCompany = new FinancingCompany($params);

        if ($financingCompany->saveOrFail()) {
            return $financingCompany;
        } else {
            return null;
        }
    }

    /**
     * @param array $params
     * @return FinancingCompany|null
     * @throws \Throwable
     */
    public function update($params)
    {
        /** @var FinancingCompany $financingCompany */
        $financingCompany = FinancingCompany::find($params['id']);
        $financingCompany->fill($params);
        if ($financingCompany->saveOrFail()) {
            return $financingCompany;
        } else {
            return null;
        }
    }

    /**
     * @param array $params
     * @return bool|void
     * @throws \Throwable
     */
    public function delete($params)
    {
        /** @var FinancingCompany $financingCompany */
        $financingCompany = FinancingCompany::find($params['id']);

        return $financingCompany->delete();
    }

}
