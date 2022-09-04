<?php

namespace App\Repositories\Parts;

use App\Models\Parts\ListingCategoryMappings;

class ListingCategoryMappingsRepository implements ListingCategoryMappingsRepositoryInterface
{
    public function get(array $params)
    {
        $query = ListingCategoryMappings::query();
        if(isset($params['map_from'])) {
            $query->where('map_from', $params['map_from']);
        }

        return $query->first();
    }
}
