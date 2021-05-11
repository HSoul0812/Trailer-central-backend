<?php

declare(strict_types=1);

namespace App\Repositories\Feed\Mapping\Incoming;

use App\Models\Feed\Mapping\Incoming\ApiEntityReference;

class ApiEntityReferenceRepository implements ApiEntityReferenceRepositoryInterface
{
    public function updateMultiples(array $conditions, array $newData): int
    {
        return ApiEntityReference::where($conditions)->update($newData);
    }
}
