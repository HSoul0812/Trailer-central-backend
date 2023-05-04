<?php

namespace App\Repositories\Parts;

use App\Models\Parts\ListingCategoryMappings;
use InvalidArgumentException;

class ListingCategoryMappingsRepository implements ListingCategoryMappingsRepositoryInterface
{
    public function get(array $params)
    {
        $query = ListingCategoryMappings::query();
        if (isset($params['map_from']) && isset($params['type_id'])) {
            $query->where('map_from', $params['map_from'])
                ->where('type_id', $params['type_id']);
        } else {
            throw new InvalidArgumentException('Required parameters are missing');
        }

        return $query->first();
    }
}
