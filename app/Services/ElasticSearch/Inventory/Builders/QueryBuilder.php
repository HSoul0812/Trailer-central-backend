<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Exceptions\ElasticSearch\FilterNotFoundException;
use App\Models\Inventory\Geolocation\Point;
use App\Services\ElasticSearch\Inventory\FieldMapperService;
use App\Services\ElasticSearch\Inventory\InventoryQueryBuilderInterface;
use App\Services\ElasticSearch\Inventory\Parameters\FilterGroup;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Filter;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Term;
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
        'status' => [
            'terms' => [
                'field' => 'status',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'dry_weight' => [
            'stats' => [
                'field' => 'dryWeight'
            ]
        ],
        'is_featured' => [
            'terms' => [
                'field' => 'isFeatured',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
        'gvwr' => [
            'stats' => [
                'field' => 'gvwr'
            ]
        ],
        'fuel_type' => [
            'terms' => [
                'field' => 'fuelType',
                'size' => self::AGGREGATION_SIZE
            ]
        ],
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
        'mileage_miles' => [
            'stats' => [
                'field' => 'mileageMiles'
            ]
        ],
        'mileage_kilometres' => [
            'stats' => [
                'field' => 'mileageKilometres'
            ]
        ],
        'is_rental' => [
            'terms' => [
                'field' => 'isRental',
                'size' => self::AGGREGATION_SIZE
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

    /** @var GeolocationInterface */
    private $geolocation;

    public function __construct(FieldMapperService $mapper)
    {
        $this->mapper = $mapper;
    }

    public function addDealers(array $dealerIds): QueryBuilderInterface
    {
        collect($dealerIds)->each(function ($term) {
            $term = Term::fromArray($term);
            $type = $term->getOperator() === Term::OPERATOR_EQ ? 'must' : 'must_not';

            // to ensure it is always a top of terms
            array_unshift($this->query['query']['bool'][$type], [
                'terms' => [
                    'dealerId' => $term->getValues()
                ]
            ]);
        });

        return $this;
    }

    public function addGeolocation(GeolocationInterface $geolocation): QueryBuilderInterface
    {
        if ($geolocation instanceof ScatteredGeolocation) {
            $this->addScatteredQueryFunction($geolocation);
        } elseif ($geolocation instanceof GeolocationRange) {
            $this->addGeoDistanceQuery($geolocation);
        }
        $this->geolocation = $geolocation;

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

        foreach ($terms as $term) {
            $filters = FilterGroup::fromArray($term);
            $query = $this->appendQueryTo($query)($filters);
        }

        $this->query = array_merge_recursive($query, $this->query);

        $this->addAggregations(isset($this->query['post_filter']));

        return $this;
    }

    public function addSort(array $sort): QueryBuilderInterface
    {
        $sort = $this->addSortScripts($sort);

        foreach ($sort as $sortKey => $order) {
            $this->query['sort'][] = [
                $sortKey => [
                    'order' => $order
                ]
            ];
        }

        return $this;
    }

    /**
     * @param array $sort
     * @return array
     */
    private function addSortScripts(array $sort): array
    {
        if (isset($sort['status_script'])) {
            $this->addStatusSortScript($sort['status_script']);
            unset($sort['status_script']);
        }

        if (isset($sort['distance'])) {
            $this->addGeoDistanceSortScript($sort['distance']);
            unset($sort['distance']);
        }

        if (isset($sort['price'])) {
            $this->addPriceSortScript($sort['price']);
            unset($sort['price']);
        }

        if (isset($sort['numFeatures'])) {
            $this->addNumFeaturesSortScript($sort['numFeatures']);
            unset($sort['numFeatures']);
        }

        if (isset($sort['tt_sort'])) {
            $this->inRandomOrder();
            unset($sort['tt_sort']);
        }

        return $sort;
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
        return function (FilterGroup $filters) use ($query) {
            if ($filters->appendsToQuery()) {
                return array_merge_recursive($query, [
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'bool' => [
                                        $filters->getESOperatorKeyword() => $filters->getFields()->map(function (Filter $field) {
                                            return $this->mapper->getBuilder($field)->globalQuery();
                                        })
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);
            }

            $filters->getFields()->each(function (Filter $field) use (&$query) {

                if ($field->getName() === 'classifieds_site') {
                    $classifiedSitesQuery = $this->mapper->getBuilder($field)->generalQuery();

                    // to ensure it always has proper structure
                    $query = array_merge_recursive($query, [
                        'query' => [
                            'bool' => [
                                'must' => [
                                ]
                            ]
                        ]
                    ]);

                    // to ensure it will be prepend
                    array_unshift($query['query']['bool']['must'], ...$classifiedSitesQuery['query']['bool']['must']);

                    if (isset($classifiedSitesQuery['query']['bool']['must_not'])) {
                        // to ensure it always has proper structure
                        $query = array_merge_recursive($query, [
                            'query' => [
                                'bool' => [
                                    'must_not' => [
                                    ]
                                ]
                            ]
                        ]);

                        // to ensure it will be prepend
                        array_unshift($query['query']['bool']['must_not'], ...$classifiedSitesQuery['query']['bool']['must_not']);
                    }

                    return;
                }

                $query = array_merge_recursive($query, $this->mapper->getBuilder($field)->generalQuery());
            });

            return $query;
        };
    }

    private function addStatusSortScript(string $status): void
    {
        array_push($this->query['sort'], ... array_map(static function ($value) {
            if(is_numeric($value)){
                return [
                    '_script' => [
                        'type' => 'string',
                        'script' => [
                            'inline' => "doc['status'].size() != 0 && doc['status'].value == params.status ? '1': '0'", // to avoid casting issues
                            'params' => [
                                'status' => (int)$value
                            ]
                        ],
                        'order' => 'desc'
                    ]
                ];
            }

            $parts = explode(':', $value);

            return [\Str::camel($parts[0]) => ['order' => $parts[1]]];
        }, explode(',', $status)));
    }

    private function addGeoDistanceSortScript(string $order): void
    {
        if ($this->geolocation) {
            $this->query['sort'][] = [
                '_geo_distance' => [
                    'location.geo' => [
                        'lat' => $this->geolocation->lat(),
                        'lon' => $this->geolocation->lon()
                    ],
                    'order' => $order
                ]
            ];
        }
    }

    private function addPriceSortScript(string $order): void
    {
        $this->query['sort'][] = [
            '_script' => [
                'type' => 'number',
                'script' => [
                    'lang' => 'painless',
                    'source' => 'double price;
                    if(doc[\'websitePrice\'] != null){ price = doc[\'websitePrice\'].value; }
                    if(0 < doc[\'salesPrice\'].value && doc[\'salesPrice\'].value < price) { price = doc[\'salesPrice\'].value; }
                    return price;
                    '
                ],
                'order' => $order
            ]
        ];
    }

    private function addNumFeaturesSortScript(string $order): void
    {
        $this->query['sort'][] = [
            '_script' => [
                'type' => 'number',
                'script' => [
                    'lang' => 'painless',
                    'source' => 'int numFeature = 0;
                    if(doc[\'featureList.floorPlan\'] != null){ numFeature += doc[\'featureList.floorPlan\'].size(); }
                    if(doc[\'featureList.stallTack\'] != null){ numFeature += doc[\'featureList.stallTack\'].size(); }
                    if(doc[\'featureList.lq\'] != null){ numFeature += doc[\'featureList.lq\'].size(); }
                    if(doc[\'featureList.doorsWindowsRamps\'] != null){ numFeature += doc[\'featureList.doorsWindowsRamps\'].size(); }
                    return numFeature;
                    '
                ],
                'order' => $order
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
        $this->query['aggregations'] = array_merge_recursive($this->query['aggregations'] ?? [], $this->aggregations);

        if ($hasTerms) {
            $this->query['aggregations']['filter_aggregations']['aggregations'] = $this->aggregations;
            $this->query['aggregations']['selected_location_aggregations']['aggregations'] = $this->aggregations;
        }
    }

    private function addGeoDistanceQuery(GeolocationRange $geolocation): void
    {
        $geo = sprintf('%d, %d', $geolocation->lat(), $geolocation->lon());

        $query = [
            'sort' => [
                [
                    '_geo_distance' => [
                        'location.geo' => $geo,
                        'order' => 'asc',
                        'unit' => $geolocation->units(),
                        'distance_type' => 'arc'
                    ]
                ]
            ]
        ];

        if ($geolocation->appendToPostQuery()) {
            $filter = [
                'must' => [
                    [
                        'geo_distance' => [
                            'distance' => sprintf('%f%s', abs($geolocation->range()), $geolocation->units()),
                            'location.geo' => $geo
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

            $query['post_filter'] = [
                'bool' => $filter
            ];
        }


        $this->query = array_merge_recursive($query, $this->query);
    }

    public function inRandomOrder(): void
    {
        $this->query['query'] = [
            'function_score' => [
                'query' => $this->query['query'],
                "functions" => [
                    [
                        "random_score" => new \stdClass(),
                    ]
                ],
                "score_mode" => "sum",
                "boost_mode" => "replace",
            ]
        ];
    }
}
