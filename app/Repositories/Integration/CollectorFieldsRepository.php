<?php

namespace App\Repositories\Integration;

use App\Exceptions\NotImplementedException;
use App\Utilities\JsonApi\WithRequestQueryable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class CollectorFieldsRepository
 * @package App\Repositories\Integration
 */
class CollectorFieldsRepository implements CollectorFieldsRepositoryInterface
{
    use WithRequestQueryable;

    public function __construct(Builder $baseQuery)
    {
        $this->withQuery($baseQuery);
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function create($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function update($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function get($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function delete($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection|null[]
     */
    public function getAll($params)
    {
        return $this->query()->get();
    }
}
