<?php

namespace App\Services\Inventory;

use App\DTOs\Inventory\Inventory;
use GuzzleHttp\Client as GuzzleHttpClient;

class InventoryService implements InventoryServiceInterface
{
    const SEARCH_KEY_MAP = [
        'pull_type' => 'pullType',
        'manufacturer' => 'manufacturer',
        'category' => 'category',
        'condition' => 'condition',
        'location_city' => 'location.city',
        'location_region' => 'location.region',
        'zip' => 'location.postalCode',
    ];

    public function __construct(private GuzzleHttpClient $httpClient)
    {}

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function list(array $params): array
    {
        $match = [];
        foreach($params as $field => $value) {
            if(array_key_exists($field, self::SEARCH_KEY_MAP)) {
                $searchField = self::SEARCH_KEY_MAP[$field];
                $match[$searchField] = $value;
            }
        }

        $elasticSearchUrl = config('trailercentral.elasticsearch.url') . "/inventory/_search";
        $res = $this->httpClient->post($elasticSearchUrl, [
            'json' => [
                'query' => [
                    'match' => $match
                ]
            ]
        ]);
        if($res->getStatusCode() == 200) {
            $result = [];
            $resJson = json_decode($res->getBody()->getContents(), true);
            foreach($resJson['hits']['hits'] as $hit) {
                $result[] = Inventory::fromData($hit['_source']);
            }
            return $result;
        } else {
            throw new \Exception('Elastic search API responded with http code: ' . $res->getStatusCode());
        }
    }
}
