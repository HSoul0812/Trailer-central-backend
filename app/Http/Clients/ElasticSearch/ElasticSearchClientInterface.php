<?php

namespace App\Http\Clients\ElasticSearch;

use App\Services\ElasticSearch\QueryBuilderInterface;

interface ElasticSearchClientInterface
{
    public function search(string $indexName, QueryBuilderInterface $query, bool $debug): ElasticSearchQueryResult;
}
