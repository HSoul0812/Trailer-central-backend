<?php

namespace App\Http\Clients\ElasticSearch;

use App\Exceptions\ElasticSearch\ResponseException;
use App\Services\ElasticSearch\QueryBuilderInterface;
use GuzzleHttp\Client;

class ElasticSearchClient extends Client implements ElasticSearchClientInterface
{
    private const HTTP_SUCCESS = 200;

    public function search(string $indexName, QueryBuilderInterface $query, bool $debug): ElasticSearchQueryResult
    {
        if ($debug) {
            return new ElasticSearchQueryResult($query->toArray(), [], 0, []);
        }

        $response = $this->post(config('elastic.client.hosts')[0] . "/$indexName/_search", ['json' => $query->toArray()]);

        if ($response->getStatusCode() === self::HTTP_SUCCESS) {
            $json = json_decode($response->getBody()->getContents(), false);

            return new ElasticSearchQueryResult($query->toArray(), (array)$json->aggregations, $json->hits->total->value, $json->hits->hits);
        }

        throw new ResponseException($response);
    }
}
