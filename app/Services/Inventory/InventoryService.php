<?php

namespace App\Services\Inventory;

use App\DTOs\Inventory\Inventory;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;


class InventoryService implements InventoryServiceInterface
{
    const PAGE_SIZE = 10;
    const TERM_SEARCH_KEY_MAP = [
        'stalls' => 'numStalls',
        'pull_type' => 'pullType',
        'manufacturer' => 'manufacturer',
        'category' => 'category',
        'condition' => 'condition',
        'location_city' => 'location.city',
        'location_region' => 'location.region',
        'zip' => 'location.postalCode',
        //'midtack' => 'hasMidtack',
        'construction' => 'frameMaterial',
        'year' => 'year',
        //'livingquarters' => 'hasLq',
        'slideouts' => 'numSlideouts',
        'configuration' => 'loadType',
        'axles' => 'numAxles',
        'color' => 'color',
        ///'ramps' => 'hasRamps'
    ];

    const RANGE_SEARCH_KEY_MAP = [
        'price' => 'existingPrice',
        'length' => 'length',
        'width' => 'width',
        'height' => 'height',
        //'gvwr' => 'gvwr',
        //'payload_capacity' => 'payloadCapacity',
    ];

    public function __construct(private GuzzleHttpClient $httpClient)
    {}

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function list(array $params): Paginator
    {
        $pageSize = self::PAGE_SIZE;
        $queries = [];

        $queries[] = $this->termQuery('isRental', false);
        foreach(self::TERM_SEARCH_KEY_MAP as $field => $searchField) {
            $termQuery = $this->termQuery($searchField, $params[$field] ?? null);
            if($termQuery !== null) {
                $queries[] = $termQuery;
            }
        }

        foreach (self::RANGE_SEARCH_KEY_MAP as $field => $searchField) {
            $minFieldKey = "{$field}_min";
            $maxFieldKey = "{$field}_max";
            $rangeQuery = $this->rangeQuery($searchField, $params[$minFieldKey] ?? null, $params[$maxFieldKey] ?? null);
            if($rangeQuery !== null) {
                $queries[] = $rangeQuery;
            }
        }

        if(isset($params['per_page'])) {
            $pageSize = $params['per_page'];
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $elasticSearchUrl = config('trailercentral.elasticsearch.url') . "/inventory/_search";
        $res = $this->httpClient->post($elasticSearchUrl, [
            'json' => [
                'from' => max(($currentPage - 1) * $pageSize, 0),
                'size' => $pageSize,
                'query' => [
                    'bool' => [
                        'must' => $queries
                    ]
                ],
                'aggregations' => [
                    'pull_type' => ['terms' => ['field' => 'pullType']],
                    'color' =>  ['terms' => ['field' => 'color']],
                    'year' => ['terms' => ['field' => 'year']],
                    'configuration' => ['terms' => ['field' => 'loadType']],
                    'slideouts' => ['terms' => ['field' => 'numSlideouts']],
                    'length' => ['stats' => ['field' => 'length']],
                    'height_inches' => ['stats' => ['field' => 'heightInches']],
                    'axles' => ['terms' => ['field' => 'numAxles']],
                    'manufacturer' => ['terms' => ['field' => 'manufacturer']],
                    'filter_aggs' => [
                        'filter' => [
                            'bool' => [
                                'must' => $queries
                            ]
                        ],
                        'aggregations' => [
                            'pull_type' => ['terms' => ['field' => 'pullType']],
                            'color' => ['terms' => ['field' => 'color']],
                            'year' => ['terms' => ['field' => 'year']],
                            'configuration' => ['terms' => ['field' => 'loadType']],
                            'slideouts' => ['terms' => ['field' => 'numSlideouts']],
                            'length' => ['stats' => ['field' => 'length']],
                            'height_inches' => ['stats' => ['field' => 'heightInches']],
                            'axles' => ['terms' => ['field' => 'numAxles']],
                            'manufacturer' => ['terms' => ['field' => 'manufacturer']],
                            'condition' => ['terms' => ['field' => 'condition']],
                            'length_inches' => ['stats' => ['field' => 'lengthInches']],
                            'price' => ['stats' => ['field' => 'existingPrice']],
                            'width' => ['stats' => ['field' => 'width']],
                            'width_inches' => ['stats' => ['field' => 'widthInches']],
                            'dealer_location_id' => ['terms' => ['field' => 'dealerLocationId']],
                            'construction' => ['terms' => ['field' => 'frameMaterial']],
                            'category' => ['terms' => ['field' => 'category']],
                            'stalls' => ['terms' => ['field' => 'numStalls']],
                            'height' => ['stats' => ['field' => 'height']]
                        ]
                    ]
                ]
            ]
        ]);
        if($res->getStatusCode() == 200) {
            $result = [];
            $resJson = json_decode($res->getBody()->getContents(), true);
            foreach($resJson['hits']['hits'] as $hit) {
                $result[] = Inventory::fromData($hit['_source']);
            }
            return new LengthAwarePaginator($result, $resJson['hits']['total'], $pageSize, $currentPage);
        } else {
            throw new \Exception('Elastic search API responded with http code: ' . $res->getStatusCode());
        }
    }

    private function rangeQuery(string $fieldKey, $min, $max): ?array {
        if($min != null || $max != null) {
            $rangeQuery = [
                'range' => [
                    $fieldKey => []
                ]
            ];
            if($min != null) {
                $rangeQuery['range'][$fieldKey]['gte'] = $min;
            }
            if($max != null) {
                $rangeQuery['range'][$fieldKey]['lte'] = $max;
            }

            return $rangeQuery;
        }
        return null;
    }

    private function termQuery(string $fieldKey, $value): ?array {
        if($value != null) {
            return [
                [
                    'term' => [
                        $fieldKey => $value
                    ]
                ]
            ];
        }
        return null;
    }
}
