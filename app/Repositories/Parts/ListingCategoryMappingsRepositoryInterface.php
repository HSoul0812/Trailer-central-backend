<?php

namespace App\Repositories\Parts;

use App\Models\Parts\ListingCategoryMappings;

interface ListingCategoryMappingsRepositoryInterface
{
    public function get(array $params);
}
