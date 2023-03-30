<?php

namespace App\Http\Clients\ElasticSearch;

use App\Exceptions\ElasticSearch\BadRequestException;
use App\Exceptions\ElasticSearch\ResponseException;
use App\Services\ElasticSearch\QueryBuilderInterface;
use GuzzleHttp\Client;

class ElasticSearchClient extends Client implements ElasticSearchClientInterface
{
    private const HTTP_SUCCESS = 200;

    private const HTTP_BAD_REQUEST = 400;

    public function search(string $indexName, QueryBuilderInterface $query, bool $debug): ElasticSearchQueryResult
    {
        if ($debug) {
            return new ElasticSearchQueryResult($query->toArray(), [], 0, []);
        }

        $response = $this->post(config('elastic.client.hosts')[0] . "/$indexName/_search", ['json' => $query->toArray(), 'http_errors' => false]);

        if ($response->getStatusCode() === self::HTTP_SUCCESS) {
            $json = json_decode($response->getBody()->getContents(), false);
            $total = is_object($json->hits->total) ? $json->hits->total->value: $json->hits->total;

            return new ElasticSearchQueryResult($query->toArray(), (array)$json->aggregations, $total , $json->hits->hits);
        }

        if ($response->getStatusCode() === self::HTTP_BAD_REQUEST) {
            $exception = new BadRequestException($response);
            if ($exception->isParseException()) {
                $exception->throwAsServerError();
            }
        }

        throw new ResponseException($response);
    }
}
