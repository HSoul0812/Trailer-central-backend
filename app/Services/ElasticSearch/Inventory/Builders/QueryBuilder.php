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

    public function __construct(FieldMapperService $mapper)
    {
        $this->mapper = $mapper;
    }

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
        'aggregations' => [
            'filter_aggregations' => [
                'aggregations' => [
                    'sleeping_capacity' => [
                        'terms' => [
                            'field' => 'numSleeps',
                            'size' => 100
                        ]
                    ],
                    'is_special' => [
                        'terms' => [
                            'field' => 'isSpecial',
                            'size' => 100
                        ]
                    ],
                    'category' => [
                        'terms' => [
                            'field' => 'category',
                            'size' => 100
                        ]
                    ],
                    'condition' => [
                        'terms' => [
                            'field' => 'condition',
                            'size' => 100
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
                            'size' => 100
                        ]
                    ],
                    'pull_type' => [
                        'terms' => [
                            'field' => 'pullType',
                            'size' => 100
                        ]
                    ],
                    'stalls' => [
                        'terms' => [
                            'field' => 'numStalls',
                            'size' => 100
                        ]
                    ],
                    'livingquarters' => [
                        'terms' => [
                            'field' => 'hasLq',
                            'size' => 100
                        ]
                    ],
                    'slideouts' => [
                        'terms' => [
                            'field' => 'numSlideouts',
                            'size' => 100
                        ]
                    ],
                    'configuration' => [
                        'terms' => [
                            'field' => 'loadType',
                            'size' => 100
                        ]
                    ],
                    'midtack' => [
                        'terms' => [
                            'field' => 'hasMidtack',
                            'size' => 100
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
                            'size' => 100
                        ]
                    ],
                    'brand' => [
                        'terms' => [
                            'field' => 'brand',
                            'size' => 100
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
                            'size' => 100,
                            'order' => [
                                '_term' => 'desc'
                            ]
                        ]
                    ],
                    'axles' => [
                        'terms' => [
                            'field' => 'numAxles',
                            'size' => 100
                        ]
                    ],
                    'construction' => [
                        'terms' => [
                            'field' => 'frameMaterial',
                            'size' => 100
                        ]
                    ],
                    'color' => [
                        'terms' => [
                            'field' => 'color',
                            'size' => 100
                        ]
                    ],
                    'ramps' => [
                        'terms' => [
                            'field' => 'hasRamps',
                            'size' => 100
                        ]
                    ],
                    'floor_plans' => [
                        'terms' => [
                            'field' => 'featureList.floorPlan',
                            'size' => 100
                        ]
                    ],
                    'passengers' => [
                        'stats' => [
                            'field' => 'numPassengers'
                        ]
                    ]
                ]
            ],
            'location_aggregations' => [
                'aggregations' => [
                    'sleeping_capacity' => [
                        'terms' => [
                            'field' => 'numSleeps',
                            'size' => 100
                        ]
                    ],
                    'is_special' => [
                        'terms' => [
                            'field' => 'isSpecial',
                            'size' => 100
                        ]
                    ],
                    'category' => [
                        'terms' => [
                            'field' => 'category',
                            'size' => 100
                        ]
                    ],
                    'condition' => [
                        'terms' => [
                            'field' => 'condition',
                            'size' => 100
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
                            'size' => 100
                        ]
                    ],
                    'pull_type' => [
                        'terms' => [
                            'field' => 'pullType',
                            'size' => 100
                        ]
                    ],
                    'stalls' => [
                        'terms' => [
                            'field' => 'numStalls',
                            'size' => 100
                        ]
                    ],
                    'livingquarters' => [
                        'terms' => [
                            'field' => 'hasLq',
                            'size' => 100
                        ]
                    ],
                    'slideouts' => [
                        'terms' => [
                            'field' => 'numSlideouts',
                            'size' => 100
                        ]
                    ],
                    'configuration' => [
                        'terms' => [
                            'field' => 'loadType',
                            'size' => 100
                        ]
                    ],
                    'midtack' => [
                        'terms' => [
                            'field' => 'hasMidtack',
                            'size' => 100
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
                            'size' => 100
                        ]
                    ],
                    'brand' => [
                        'terms' => [
                            'field' => 'brand',
                            'size' => 100
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
                            'size' => 100,
                            'order' => [
                                '_term' => 'desc'
                            ]
                        ]
                    ],
                    'axles' => [
                        'terms' => [
                            'field' => 'numAxles',
                            'size' => 100
                        ]
                    ],
                    'construction' => [
                        'terms' => [
                            'field' => 'frameMaterial',
                            'size' => 100
                        ]
                    ],
                    'color' => [
                        'terms' => [
                            'field' => 'color',
                            'size' => 100
                        ]
                    ],
                    'ramps' => [
                        'terms' => [
                            'field' => 'hasRamps',
                            'size' => 100
                        ]
                    ],
                    'floor_plans' => [
                        'terms' => [
                            'field' => 'featureList.floorPlan',
                            'size' => 100
                        ]
                    ],
                    'passengers' => [
                        'stats' => [
                            'field' => 'numPassengers'
                        ]
                    ]
                ]
            ],
            'sleeping_capacity' => [
                'terms' => [
                    'field' => 'numSleeps',
                    'size' => 100
                ]
            ],
            'is_special' => [
                'terms' => [
                    'field' => 'isSpecial',
                    'size' => 100
                ]
            ],
            'category' => [
                'terms' => [
                    'field' => 'category',
                    'size' => 100
                ]
            ],
            'condition' => [
                'terms' => [
                    'field' => 'condition',
                    'size' => 100
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
                    'size' => 100
                ]
            ],
            'pull_type' => [
                'terms' => [
                    'field' => 'pullType',
                    'size' => 100
                ]
            ],
            'stalls' => [
                'terms' => [
                    'field' => 'numStalls',
                    'size' => 100
                ]
            ],
            'livingquarters' => [
                'terms' => [
                    'field' => 'hasLq',
                    'size' => 100
                ]
            ],
            'slideouts' => [
                'terms' => [
                    'field' => 'numSlideouts',
                    'size' => 100
                ]
            ],
            'configuration' => [
                'terms' => [
                    'field' => 'loadType',
                    'size' => 100
                ]
            ],
            'midtack' => [
                'terms' => [
                    'field' => 'hasMidtack',
                    'size' => 100
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
                    'size' => 100
                ]
            ],
            'brand' => [
                'terms' => [
                    'field' => 'brand',
                    'size' => 100
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
                    'size' => 100,
                    'order' => [
                        '_term' => 'desc'
                    ]
                ]
            ],
            'axles' => [
                'terms' => [
                    'field' => 'numAxles',
                    'size' => 100
                ]
            ],
            'construction' => [
                'terms' => [
                    'field' => 'frameMaterial',
                    'size' => 100
                ]
            ],
            'color' => [
                'terms' => [
                    'field' => 'color',
                    'size' => 100
                ]
            ],
            'ramps' => [
                'terms' => [
                    'field' => 'hasRamps',
                    'size' => 100
                ]
            ],
            'floor_plans' => [
                'terms' => [
                    'field' => 'featureList.floorPlan',
                    'size' => 100
                ]
            ],
            'passengers' => [
                'stats' => [
                    'field' => 'numPassengers'
                ]
            ]
        ],
        'stored_fields' => [
            '_source'
        ],
        'script_fields' => []
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

        foreach ($terms as $term => $data) {
            $query = $this->appendQueryTo($query)($term, $data);
        }

        $this->query = array_merge_recursive($query, $this->query);

        return $this;
    }

    public function addSort(array $sort): QueryBuilderInterface
    {
        return $this;
    }

    public function addPagination(array $pagination): QueryBuilderInterface
    {
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
}
