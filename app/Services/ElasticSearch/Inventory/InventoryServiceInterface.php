<?php

namespace App\Services\ElasticSearch\Inventory;

use App\Http\Clients\ElasticSearch\ElasticSearchQueryResult;
use App\Services\ElasticSearch\Inventory\Parameters\Geolocation\GeolocationInterface;

interface InventoryServiceInterface
{
    public function search(array                $dealerIds,
                           array                $terms,
                           GeolocationInterface $geolocation,
                           array                $sort = [],
                           array                $pagination = [],
                           bool                 $debug = false): ElasticSearchQueryResult;
}
