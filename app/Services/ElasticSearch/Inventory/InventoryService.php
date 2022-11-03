<?php

namespace App\Services\ElasticSearch\Inventory;

use App\Http\Clients\ElasticSearch\ElasticSearchClient;
use App\Http\Clients\ElasticSearch\ElasticSearchQueryResult;
use App\Services\ElasticSearch\Inventory\Parameters\DealerId;
use App\Services\ElasticSearch\Inventory\Parameters\Geolocation\GeolocationInterface;

class InventoryService implements InventoryServiceInterface
{
    /** @var ElasticSearchClient */
    private $client;

    /** @var InventoryQueryBuilderInterface */
    private $queryBuilder;

    /**
     * @param ElasticSearchClient $client
     * @param InventoryQueryBuilderInterface $queryBuilder
     */
    public function __construct(ElasticSearchClient $client, InventoryQueryBuilderInterface $queryBuilder)
    {
        $this->client = $client;
        $this->queryBuilder = $queryBuilder;
    }

    public function search(bool                 $inRandomOrder,
                           DealerId             $dealerIds,
                           array                $terms,
                           GeolocationInterface $geolocation,
                           array                $sort = [],
                           array                $pagination = []): ElasticSearchQueryResult
    {
        $query = $this->queryBuilder->addDealers($dealerIds)
            ->addTerms($terms)
            ->addGeolocation($geolocation)
            ->addSort($sort)
            ->addPagination($pagination)
            ->inRandomOrder($inRandomOrder);

        return $this->client->search((string)config('elastic.scout_driver.indices.inventory'), $query);
    }
}
