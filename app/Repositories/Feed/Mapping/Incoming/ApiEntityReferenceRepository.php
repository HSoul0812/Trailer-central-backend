<?php

declare(strict_types=1);

namespace App\Repositories\Feed\Mapping\Incoming;

use App\Models\Feed\Mapping\Incoming\ApiEntityReference;
use App\Repositories\RepositoryAbstract;

class ApiEntityReferenceRepository extends RepositoryAbstract implements ApiEntityReferenceRepositoryInterface
{
    /**
     * @param array $params
     * @return ApiEntityReference|null
     */
    public function get($params): ?ApiEntityReference
    {
        $query = ApiEntityReference::query();

        if (isset($params['entity_id'])) {
            $query->where('entity_id', '=', $params['entity_id']);
        }

        if (isset($params['reference_id'])) {
            $query->where('entity_id', '=', $params['entity_id']);
        }

        if (isset($params['entity_type'])) {
            $query->where('entity_id', '=', $params['entity_id']);
        }

        if (isset($params['api_key'])) {
            $query->where('entity_id', '=', $params['entity_id']);
        }

        return $query->first();
    }

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
