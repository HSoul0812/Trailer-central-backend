<?php

namespace App\Services\ElasticSearch;

use App\Services\ElasticSearch\Inventory\Parameters\DealerId;
use App\Services\ElasticSearch\Inventory\Parameters\Geolocation\GeolocationInterface;

interface QueryBuilderInterface
{
    public function addGeolocation(GeolocationInterface $geolocation): self;

    public function addDealers(DealerId $dealerIds): self;

    public function addTerms(array $terms): self;

    public function addSort(array $sort): self;

    public function addPagination(array $pagination): self;

    public function toArray(): array;
}
