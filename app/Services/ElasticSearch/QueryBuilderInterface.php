<?php

namespace App\Services\ElasticSearch;

use App\Models\Inventory\Geolocation\Point;

interface QueryBuilderInterface
{
    public function addDistance(Point $location): self;

    public function addDealers(array $dealerIds): self;

    public function addTerms(array $terms): self;

    public function addSort(array $sort): self;

    public function addPagination(array $pagination): self;

    public function toArray(): array;
}
