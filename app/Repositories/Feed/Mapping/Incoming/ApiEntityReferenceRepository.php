<?php

declare(strict_types=1);

namespace App\Repositories\Feed\Mapping\Incoming;

use App\Models\Feed\Mapping\Incoming\ApiEntityReference;
use App\Repositories\RepositoryAbstract;

class ApiEntityReferenceRepository extends RepositoryAbstract implements ApiEntityReferenceRepositoryInterface
{
    /**
     * @param $params
     * @return ApiEntityReference
     */
    public function create($params): ApiEntityReference
    {
        $apiEntityReference = new ApiEntityReference($params);
        $apiEntityReference->save();

        return $apiEntityReference;
    }

    public function updateMultiples(array $conditions, array $newData): int
    {
        return ApiEntityReference::where($conditions)->update($newData);
    }
}
