<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Exceptions\ElasticSearch\FilterNotFoundException;
use App\Models\Inventory\Geolocation\Point;
use App\Services\ElasticSearch\Inventory\FieldMapperService;
use App\Services\ElasticSearch\Inventory\InventoryQueryBuilderInterface;
use App\Services\ElasticSearch\QueryBuilderInterface;

class QueryBuilder implements InventoryQueryBuilderInterface
{
    /**  @var FieldMapperService */
    private $mapper;

    private const AGGREGATION_SIZE = 200;

    public function __construct(FieldMapperService $mapper)
    {
        $this->mapper = $mapper;
    }

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
                    [
                        'term' => [
                            'isArchived' => false
                        ]
                    ],
                    [
                        'term' => [
                            'showOnWebsite' => true
                        ]
                    ]
                ],
                'must_not' => [
                    [
                        'term' => [
                            'status' => 6
                        ]
                    ]
                ]
            ]
        ],
        'stored_fields' => [
            '_source'
        ],
        'script_fields' => [],
        'sort' => []
    ];

    public function addDealers(array $dealerIds): QueryBuilderInterface
    {

        if (!empty($dealerIds)) {
            $this->query['query']['bool']['must'] = [
                [
                    'terms' => [
                        'dealerId' => $dealerIds
                    ]
                ]
            ];
        }

        return $this;
    }

    public function addDistance(Point $location): QueryBuilderInterface
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

    /**
     * @param array $terms
     * @return QueryBuilderInterface
     *
     * @throws FilterNotFoundException when the filter was not able to be handled
     */
    public function addTerms(array $terms): QueryBuilderInterface
    {
        $query = [];

        $this->addAggregations(count($terms) > 0);

        foreach ($terms as $term => $data) {
            $query = $this->appendQueryTo($query)($term, $data);
        }

        $this->query = array_merge_recursive($query, $this->query);

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

    private function addStatusSortScript(string $status)
    {
        array_push($this->query['sort'], ... array_map(function ($value) {
            return [
                '_script' => [
                    'type' => 'string',
                    'script' => [
                        'inline' => "doc['status'].value == params.status",
                        'params' => [
                            'status' => (int)$value
                        ]
                    ],
                    'order' => 'desc'
                ]
            ];
        }, explode(',', $status)));
    }

    private function addLocationSortScript(string $locations)
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

    private function addAggregations(bool $hasTerms)
    {
        $this->query['aggregations'] = $this->aggregations;

        if ($hasTerms) {
            $this->query['aggregations']['filter_aggregations'] = $this->aggregations;
            $this->query['aggregations']['location_aggregations'] = $this->aggregations;
        }
    }
}
