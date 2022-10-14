<?php

namespace App\Services\ElasticSearch\Inventory;

use App\Http\Clients\ElasticSearch\ElasticSearchQueryResult;
use App\Models\Inventory\Geolocation\Point;

interface InventoryServiceInterface
{
    public function search(array $dealerIds,
                           array $terms,
                           Point $location,
                           array $sort = [],
                           array $pagination = []): ElasticSearchQueryResult;
}
