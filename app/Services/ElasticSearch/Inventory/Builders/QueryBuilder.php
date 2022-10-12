<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Exceptions\ElasticSearch\FilterNotFoundException;
use App\Models\Inventory\Geolocation\Point;
use App\Services\ElasticSearch\Inventory\FieldMapperService;
use App\Services\ElasticSearch\Inventory\InventoryQueryBuilderInterface;
use App\Services\ElasticSearch\Inventory\Parameters\DealerId;
use App\Services\ElasticSearch\Inventory\Parameters\Geolocation\GeolocationInterface;
use App\Services\ElasticSearch\Inventory\Parameters\Geolocation\GeolocationRange;
use App\Services\ElasticSearch\Inventory\Parameters\Geolocation\ScatteredGeolocation;
use App\Services\ElasticSearch\QueryBuilderInterface;

class QueryBuilder implements InventoryQueryBuilderInterface
{
    /**  @var FieldMapperService */
    private $mapper;

    private const AGGREGATION_SIZE = 200;

    private $aggregations = [
        'sleeping_capacity' => [
            'terms' => [
                'field' => 'numSleeps',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'is_special' => [
            'terms' => [
                'field' => 'isSpecial',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'category' => [
            'terms' => [
                'field' => 'category',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'condition' => [
            'terms' => [
                'field' => 'condition',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'length' => [
            'stats' => [
                'field' => 'length'
            ]
        ],
        'length_inches' => [
            'stats' => [
                'field' => 'lengthInches'
            ]
        ],
        'width_inches' => [
            'stats' => [
                'field' => 'widthInches'
            ]
        ],
        'width' => [
            'stats' => [
                'field' => 'width'
            ]
        ],
        'height' => [
            'stats' => [
                'field' => 'height'
            ]
        ],
        'height_inches' => [
            'stats' => [
                'field' => 'heightInches'
            ]
        ],
        'dealer_location_id' => [
            'terms' => [
                'field' => 'dealerLocationId',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'pull_type' => [
            'terms' => [
                'field' => 'pullType',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'stalls' => [
            'terms' => [
                'field' => 'numStalls',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'livingquarters' => [
            'terms' => [
                'field' => 'hasLq',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'slideouts' => [
            'terms' => [
                'field' => 'numSlideouts',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'configuration' => [
            'terms' => [
                'field' => 'loadType',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'midtack' => [
            'terms' => [
                'field' => 'hasMidtack',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'payload_capacity' => [
            'stats' => [
                'field' => 'payloadCapacity'
            ]
        ],
        'manufacturer' => [
            'terms' => [
                'field' => 'manufacturer',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'brand' => [
            'terms' => [
                'field' => 'brand',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'price' => [
            'stats' => [
                'field' => 'basicPrice'
            ]
        ],
        'year' => [
            'terms' => [
                'field' => 'year',
                'size' => self::AGGREGATION_SIZE,
                'order' => [
                    '_term' => 'desc'
                ]
            ]
        ],
        'axles' => [
            'terms' => [
                'field' => 'numAxles',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'construction' => [
            'terms' => [
                'field' => 'frameMaterial',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'color' => [
            'terms' => [
                'field' => 'color',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'ramps' => [
            'terms' => [
                'field' => 'hasRamps',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'floor_plans' => [
            'terms' => [
                'field' => 'featureList.floorPlan',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'passengers' => [
            'stats' => [
                'field' => 'numPassengers'
            ]
        ]
    ];

    private $query = [
        'query' => [
            'bool' => [
                'must' => [
                ],
                'must_not' => [
                ]
            ]
        ],
        'stored_fields' => [
            '_source'
        ],
        'script_fields' => [],
        'sort' => []
    ];

    public function __construct(FieldMapperService $mapper)
    {
        $this->mapper = $mapper;
    }

    public function addDealers(DealerId $dealerIds): QueryBuilderInterface
    {
        if (!empty($dealerIds->includeIds())) {
            $this->query['query']['bool']['must'][] = [
                'terms' => [
                    'dealerId' => $dealerIds->includeIds()
                ]
            ];
        }

        if (!empty($dealerIds->excludeIds())) {
            $this->query['query']['bool']['must_not'][] = [
                'terms' => [
                    'dealerId' => $dealerIds->excludeIds()
                ]
            ];
        }

        return $this;
    }

    public function addGeolocation(GeolocationInterface $geolocation): QueryBuilderInterface
    {
        if ($geolocation instanceof ScatteredGeolocation) {
            $this->addScatteredQueryFunction($geolocation);
        } elseif ($geolocation instanceof GeolocationRange) {
            $this->addGeoDistanceQuery($geolocation);
        }

        return $this->addDistanceScript($geolocation->toPoint());
    }

    /**
     * @param array $terms
     * @return QueryBuilderInterface
     *
     * @throws FilterNotFoundException when the filter was not able to be handled
     */
    public function addTerms(array $terms): QueryBuilderInterface
    {
        $query = [];

        foreach ($terms as $term => $data) {
            $query = $this->appendQueryTo($query)($term, $data);
        }

        $this->query = array_merge_recursive($query, $this->query);

        $this->addAggregations(isset($this->query['post_filter']));

        return $this;
    }

    public function addSort(array $sort): QueryBuilderInterface
    {
        if (isset($sort['status_script'])) {
            $this->addStatusSortScript($sort['status_script']);
            unset($sort['status_script']);
        }

        if (isset($sort['location_script'])) {
            $this->addLocationSortScript($sort['location_script']);
            unset($sort['location_script']);
        }

        foreach ($sort as $sortKey => $order) {
            $this->query['sort'][] = [
                $sortKey => [
                    'order' => $order
                ]
            ];
        }

        return $this;
    }

    public function addPagination(array $pagination): QueryBuilderInterface
    {
        $this->query['from'] = $pagination['offset'];
        $this->query['size'] = $pagination['per_page'];

        return $this;
    }

    public function toArray(): array
    {
        return $this->query;
    }

    private function appendQueryTo(array $query): callable
    {
        return function (string $term, string $data) use ($query) {
            return array_merge_recursive($query, $this->mapper->getBuilder($term, $data)->query());
        };
    }

    private function addStatusSortScript(string $status): void
    {
        array_push($this->query['sort'], ... array_map(static function ($value) {
            return [
                '_script' => [
                    'type' => 'string',
                    'script' => [
                        'inline' => "doc['status'].value == params.status ? '1': '0'", // to avoid casting issues
                        'params' => [
                            'status' => (int)$value
                        ]
                    ],
                    'order' => 'desc'
                ]
            ];
        }, explode(',', $status)));
    }

    private function addLocationSortScript(string $locations): void
    {
        $this->query['sort'][] = [
            '_script' => [
                'type' => 'number',
                'script' => [
                    'inline' => "if(doc['dealerLocationId'].value != null) { for(int i=0; i < params['locations'].length; i++) {if(params['locations'][i] == doc['dealerLocationId'].value) return -1;} return 0;} else { return 1; }",
                    'params' => [
                        'locations' => array_map('intval', explode(',', $locations))
                    ]
                ],
                'order' => 'asc'
            ]
        ];
    }

    private function addDistanceScript(Point $location): QueryBuilderInterface
    {
        $this->query['script_fields']['distance'] = [
            'script' => [
                'source' => "if(doc['location.geo'].value != null) {
                                return doc['location.geo'].planeDistance(params.lat, params.lng) * 0.000621371;
                             } else {
                                return 0;
                             }",
                'params' => [
                    'lat' => $location->latitude,
                    'lng' => $location->longitude
                ]
            ]
        ];

        return $this;
    }

    private function addScatteredQueryFunction(ScatteredGeolocation $geolocation): void
    {
        $filter = [
            'must' => [
                [
                    'function_score' => [
                        'functions' => [
                            [
                                'random_score' => [
                                    'seed' => 10,
                                    'field' => '_seq_no'
                                ],
                                'weight' => 1
                            ],
                            [
                                'script_score' => [
                                    'script' => [
                                        'source' => "double d; if(doc['location.geo'].value != null) { d = doc['location.geo'].planeDistance(params.lat, params.lng) * 0.000621371; } else { return 0.1; } if(d >= (params.grouping*params.fromScore)) { return 0.2; } else { return params.fromScore - Math.floor(d/params.grouping); }",
                                        'params' => [
                                            'lat' => $geolocation->lat(),
                                            'lng' => $geolocation->lon(),
                                            'fromScore' => 100,
                                            'grouping' => $geolocation->grouping()
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'boost_mode' => 'replace',
                        'score_mode' => 'sum'
                    ]
                ]
            ]
        ];

        if (isset($this->query['query']['bool'])) {
            $filter['must'][] = [
                'bool' => $this->query['query']['bool']
            ];
            unset($this->query['query']['bool']);
        }

        $this->query = array_merge_recursive([
            'query' => [
                'bool' => $filter
            ]
        ], $this->query);
    }

    private function addAggregations(bool $hasTerms): void
    {
        $this->query['aggregations'] = array_merge_recursive($this->query['aggregations'], $this->aggregations);

        if ($hasTerms) {
            $this->query['aggregations']['filter_aggregations']['aggregations'] = $this->aggregations;
            $this->query['aggregations']['selected_location_aggregations']['aggregations'] = $this->aggregations;
        }
    }

    private function addGeoDistanceQuery(GeolocationRange $geolocation): void
    {
        $lonLat = sprintf('%d, %d', $geolocation->lon(), $geolocation->lat());

        $filter = [
            'must' => [
                [
                    'geo_distance' => [
                        'distance' => sprintf('%d%s', $geolocation->range(), $geolocation->units()),
                        'location.geo' => $lonLat
                    ]
                ]
            ]
        ];

        if (isset($this->query['post_filter']['bool'])) {
            $filter['must'][] = [
                'bool' => $this->query['post_filter']['bool']
            ];
            unset($this->query['post_filter']['bool']);
        }

        $this->query = array_merge_recursive([
            'post_filter' => [
                'bool' => $filter
            ],
            'sort' => [
                [
                    '_geo_distance' => [
                        'location.geo' => $lonLat,
                        'order' => $geolocation->sort(),
                        'unit' => $geolocation->units(),
                        'distance_type' => 'arc'
                    ]
                ]
            ]
        ], $this->query);
    }
}
