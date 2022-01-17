<?php

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpKernel\Exception\HttpException;

<<<<<<< HEAD
namespace App\Services\Inventory;

use App\DTOs\Inventory\Inventory;
use App\DTOs\Inventory\InventoryListResponse;
use App\Models\Geolocation\Geolocation;
use App\Repositories\Geolocation\GeolocationRepositoryInterface;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class SearchQueryBuilder {
    private array $queries = [];
    private bool $willPaginate = false;
    private ?int $page = null;
    private ?int $pageSize = null;
    private ?array $globalAggregations = null;
    private ?array $filterAggregations = null;
    private ?array $geoScore = null;

    public function getWillPaginate(): bool {
        return $this->willPaginate;
    }

    public function getPage(): ?int {
        return $this->page;
    }

    public function getPageSize(): ?int {
        return $this->pageSize;
    }

    public function rangeQuery(string $fieldKey, $min, $max) {
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

            $this->queries[] = $rangeQuery;
        }
        return $this;
    }

    public function termQuery(string $fieldKey, $value) {
        if($value != null) {
            $this->queries[] = [
                [
                    'term' => [
                        $fieldKey => $value
                    ]
                ]
            ];
        }
        return $this;
    }

    public function geoScoring(float $lat, float $lng) {
        $this->geoScore = [
            'lat' => $lat,
            'lon' => $lng
        ];
        return $this;
    }

    public function globalAggregate(array $aggregations) {
        $this->globalAggregations = $aggregations;
        return $this;
    }

    public function filterAggregate(array $aggregations) {
        $this->filterAggregations = $aggregations;
        return $this;
    }

    public function paginate(int $page, int $pageSize) {
        $this->willPaginate = true;
        $this->page = $page;
        $this->pageSize = $pageSize;
    }

    public function build(): array {
        $result = [];

        if($this->willPaginate) {
            $result['from'] = max(($this->page - 1) * $this->pageSize, 0);
            $result['size'] = $this->pageSize;
        }

        if(count($this->queries) > 0) {
            if($this->geoScore) {
                $result['query'] = [
                    'function_score' => [
                        'query' => [
                            'bool' => [
                                'must' => $this->queries
                            ]
                        ],
                        'script_score' => [
                            'source' => "double d; if(doc['location.geo'].value != null) { d = doc['location.geo'].planeDistance(params.lat, params.lng) * 0.000621371; } else { return 0.1; } if(d >= (params.grouping*params.fromScore)) { return 0.2; } else { return params.fromScore - Math.floor(d\/params.grouping); ",
                            'params' => [
                                "lat" => $this->geoScore['lat'],
							    "lng" => $this->geoScore['lon'],
							    "fromScore" => 100,
							    "grouping" => 60
                            ]
                        ]
                    ]

                ];
            } else {
                $result['query'] = [
                    'bool' => [
                        'must' => $this->queries
                    ]
                ];
            }
        }

        $result['aggregations'] = array_merge(
            [],
            $this->globalAggregations ?? [],
            $this->filterAggregations ? [
                'filter_aggs' => [
                    'filter' => [
                        'bool' => ['must' => $this->queries]
                    ],
                    'aggregations' => $this->filterAggregations
                ]
            ] : []
        );
        return $result;
    }
}

class InventoryService implements InventoryServiceInterface
{
    const DEFAULT_COUNTRY = 'USA';
    const PAGE_SIZE = 10;
    const TERM_SEARCH_KEY_MAP = [
        'stalls' => 'numStalls',
        'pull_type' => 'pullType',
        'manufacturer' => 'manufacturer',
        'category' => 'category',
        'condition' => 'condition',
        'construction' => 'frameMaterial',
        'year' => 'year',
        'slideouts' => 'numSlideouts',
        'configuration' => 'loadType',
        'axles' => 'numAxles',
        'color' => 'color',
    ];

    const RANGE_SEARCH_KEY_MAP = [
        'price' => 'existingPrice',
        'length' => 'length',
        'width' => 'width',
        'height' => 'height',
    ];

    public function __construct(private GuzzleHttpClient $httpClient, private GeolocationRepositoryInterface $geolocationRepository)
    {}

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function list(array $params): InventoryListResponse
    {
        $queryBuilder = $this->buildSearchQuery($params);
        $elasticSearchUrl = config('trailercentral.elasticsearch.url') . "/inventory/_search";
        $res = $this->httpClient->post($elasticSearchUrl, [
            'json' => $queryBuilder->build()
        ]);

        if($res->getStatusCode() == 200) {
            $result = [];
            $resJson = json_decode($res->getBody()->getContents(), true);
            foreach($resJson['hits']['hits'] as $hit) {
                $result[] = Inventory::fromData($hit['_source']);
            }

            $paginator = new LengthAwarePaginator(
                $result,
                $resJson['hits']['total'],
                $queryBuilder->getPageSize(),
                $queryBuilder->getPage()
            );

            $response = new InventoryListResponse();
            $response->aggregations = $resJson['aggregations'];
            $response->inventories = $paginator;
            return $response;
        } else {
            throw new \Exception('Elastic search API responded with http code: ' . $res->getStatusCode());
        }
    }

    #[ArrayShape(['from' => "int", 'size' => "int", 'query' => "array[]", 'aggregations' => "array"])]
    private function buildSearchQuery(array $params): SearchQueryBuilder {
        $queryBuilder = new SearchQueryBuilder();
        $location = $this->getGeolocation($params);

        $this->buildTermQueries($queryBuilder, $params);
        $this->buildRangeQueries($queryBuilder, $params);
        $this->buildAggregations($queryBuilder, $params);
        $this->buildPaginateQuery($queryBuilder, $params);

        if($location) {
            $this->buildGeoScoring($queryBuilder, $location);
        }

        return $queryBuilder;
    }

    private function buildGeoScoring(SearchQueryBuilder $queryBuilder, Geolocation $location) {
        $queryBuilder->geoScoring($location->latitude, $location->longitude);
    }

    private function buildAggregations(SearchQueryBuilder $queryBuilder, array $params) {
        $queryBuilder->globalAggregate([
            'pull_type' => ['terms' => ['field' => 'pullType']],
            'color' =>  ['terms' => ['field' => 'color']],
            'year' => ['terms' => ['field' => 'year']],
            'configuration' => ['terms' => ['field' => 'loadType']],
            'slideouts' => ['terms' => ['field' => 'numSlideouts']],
            'length' => ['stats' => ['field' => 'length']],
            'height_inches' => ['stats' => ['field' => 'heightInches']],
            'axles' => ['terms' => ['field' => 'numAxles']],
            'manufacturer' => ['terms' => ['field' => 'manufacturer']]
        ]);
        $queryBuilder->filterAggregate([
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
        ]);
    }

    private function buildPaginateQuery(SearchQueryBuilder $queryBuilder, array $params) {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $queryBuilder->paginate($currentPage, $params['per_page'] ?? self::PAGE_SIZE);
    }

    private function buildTermQueries(SearchQueryBuilder $queryBuilder, array $params) {
        $queryBuilder->termQuery('isRental', false);
        foreach(self::TERM_SEARCH_KEY_MAP as $field => $searchField) {
            $queryBuilder->termQuery($searchField, $params[$field] ?? null);
        }
    }

    private function buildRangeQueries(SearchQueryBuilder $queryBuilder, array $params)
    {
        foreach (self::RANGE_SEARCH_KEY_MAP as $field => $searchField) {
            $minFieldKey = "{$field}_min";
            $maxFieldKey = "{$field}_max";
            $queryBuilder->rangeQuery($searchField, $params[$minFieldKey] ?? null, $params[$maxFieldKey] ?? null);
        }
    }

    private function getGeolocation(array $params): ?Geolocation {
        $locationType = $params['location_type'] ?? null;
        $location = null;
        try {
            if ($locationType === 'region') {
                $location = $this->geolocationRepository->get([
                    'city' => $params['location_city'] ?? null,
                    'state' => $params['location_region'] ?? null,
                    'country' => $params['location_country'] ?? self::DEFAULT_COUNTRY
                ]);
            } else if ($locationType === 'range') {
                $location = $this->geolocationRepository->get([
                    'zip' => $params['zip'] ?? null,
                ]);
            }
        } catch(ModelNotFoundException) {}
        return $location;
    }

    /**
     * @param int $id the id of the inventory
     */
    public function show(int $id): TcApiResponseInventory
    {
        $url = config('services.trailercentral.api') . 'inventory/' . $id . '?include=features';
        $inventory = $this->handleHttpRequest('GET', $url);

        return TcApiResponseInventory::fromData($inventory['data']);
    }

    /**
     * @param string $method
     * @param string $url
     *
     * @return array
     */
    #[ArrayShape([
        'data' => [[
            'title'   => 'string',
            'address' => [
                'id'               => 'int',
                'payload_capacity' => 'float',
                'url'              => 'string',
                'description'      => 'string',
                'gvwr'             => 'float',
                'weight'           => 'float',
                'width'            => 'float',
                'height'           => 'float',
                'length'           => 'float',
                'manufacturer'     => 'string',
                'created_at'       => 'string',
                'price'            => 'float',
                'sales_price'      => 'float',
                'title'            => 'string',
            ],
            'images' => [
                'image_id'     => 'int',
                'is_default'   => 'int',
                'is_secondary' => 'int',
                'position'     => 'int',
                'url'          => 'string',
            ],
            'dealer' => [
                'id'            => 'int',
                'identifier'    => 'string',
                'created_at'    => 'string',
                'name'          => 'string',
                'email'         => 'string',
                'profile_image' => 'string',
            ],
            'features' => [
                'feature_list_id' => 'int',
                'value'           => 'string',
                'feature_name'    => 'string',
            ],
            'dealer_location' => [
                'id'         => 'int',
                'identifier' => 'string',
                'contact'    => 'string',
                'name'       => 'string',
                'website'    => 'string',
                'phone'      => 'string',
                'fax'        => 'string',
                'address'    => 'string',
                'city'       => 'string',
                'county'     => 'string',
                'region'     => 'string',
                'postal'     => 'string',
                'postalcode' => 'string',
                'country'    => 'string',
                'federal_id' => 'string',
                'sales_tax'  => 'array',
            ],
            'primary_image' => [
                'image_id'     => 'int',
                'is_default'   => 'int',
                'is_secondary' => 'int',
                'position'     => 'int',
                'url'          => 'string',
            ],
        ]],
    ])]
    private function handleHttpRequest(string $method, string $url): array
    {
        try {
            $response = $this->httpClient->request($method, $url);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            \Log::info('Exception was thrown while calling TrailerCentral API.');
            \Log::info($e->getCode() . ': ' . $e->getMessage());

            throw new HttpException(422, $e->getMessage());
        }
    }
}
