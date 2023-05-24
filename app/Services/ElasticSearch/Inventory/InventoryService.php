<?php

namespace App\Services\ElasticSearch\Inventory;

use App\Http\Clients\ElasticSearch\ElasticSearchClient;
use App\Http\Clients\ElasticSearch\ElasticSearchQueryResult;
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

    public function search(
        array                $dealerIds,
        int                  $aggregationSize,
        array                $terms,
        GeolocationInterface $geolocation,
        array                $sort = [],
        array                $pagination = [],
        bool                 $debug = false): ElasticSearchQueryResult
    {
        $query = $this->queryBuilder->initializeAggregators($aggregationSize)
            ->addTerms($terms)
            ->addDealers($dealerIds)
            ->addGeolocation($geolocation)
            ->addSort($sort)
            ->addPagination($pagination);

        return $this->client->search((string)config('elastic.scout_driver.indices.inventory'), $query, $debug);
    }
}
