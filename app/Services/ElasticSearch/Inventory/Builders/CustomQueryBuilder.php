<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Models\Inventory\Inventory;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Filter;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Term;

class CustomQueryBuilder implements FieldQueryBuilderInterface
{
    /**
     * @var Filter
     */
    private $field;

    /** @var array */
    private $query = [];

    /**
     * @param Filter $field
     */
    public function __construct(Filter $field)
    {
        $this->field = $field;
    }

    /**
     * @return \array[][][][][]
     */
    private function buildImagesQuery(array $extensions): array
    {
        return [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'dis_max' => [
                                'queries' => array_map(static function ($image) {
                                    return [
                                        'wildcard' => [
                                            'image' => [
                                                'value' => '*.' . $image
                                            ]
                                        ]
                                    ];
                                }, $extensions)
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array|\array[][][][][][]
     */
    private function buildClearanceSpecialQuery(): array
    {
        return [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'bool' => [
                                'should' => [
                                    [
                                        'term' => [
                                            'isFeatured' => true
                                        ]
                                    ],
                                    [
                                        'term' => [
                                            'isSpecial' => true
                                        ]
                                    ],
                                    [
                                        'wildcard' => [
                                            'title.tokens' => [
                                                'value' => '*as is*'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private function buildLocationQueryForGlobalFilter(string $name, array $values): array
    {
        $field = str_replace('_', '.', $name);

        $query = [
            'bool' => [
                'must' => []
            ]
        ];

        foreach ($values as $value) {
            $query['bool']['must'][] = [
                'term' => [
                    $field => $value
                ]
            ];
        }

        return $query;
    }

    private function buildLocationQuery(string $name, array $values): array
    {
        $field = str_replace('_', '.', $name);

        $query = [
            'bool' => [
                'must' => []
            ]
        ];

        foreach ($values as $value) {
            $query['bool']['must'][] = [
                'term' => [
                    $field => $value
                ]
            ];
        }

        return [
            'post_filter' => $query,
            'aggregations' => [
                'filter_aggregations' => ['filter' => $query],
                'selected_location_aggregations' => ['filter' => $query]
            ]
        ];
    }

    private function buildClassifiedsSiteQuery($isClassifieds): array
    {
        // when it is not a classifieds site then it should filter by `isArchived` & `isArchived` & `status`
        $query = [
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
                                'status' => Inventory::STATUS_QUOTE
                            ]
                        ]
                    ]
                ]
            ]
        ];

        if ($isClassifieds) {
            $query['query']['bool']['must'][] = [
                'term' => [
                    'isClassified' => true
                ]
            ];
        }

        return $query;
    }

    /**
     * @return array|string[][][][][][]
     */
    private function buildEmptyImagesQuery(): array
    {
        return [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'exists' => [
                                'field' => 'image',
                            ],
                        ]
                    ],
                    'must_not' => [
                        [
                            'term' => [
                                'image' => ''
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return string[][][][][][]
     */
    private function buildAvailabilityQuery(string $operator, array $values): array
    {
        if ($operator == Term::OPERATOR_EQ) {
            return [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'terms' => [
                                                'availability' => $values
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }
        return [
            'query' => [
                'bool' => [
                    'must_not' => [
                        [
                            'terms' => [
                                'availability' => $values
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return \array[][]
     */
    private function buildSalePriceFilterScriptQuery(array $data): array
    {
        return [
            'query' => [
                'bool' => [
                    'filter' => [
                        'script' => $this->generateSalePriceFilterScript($data['sale'], $data['price'])
                    ]
                ]
            ]
        ];
    }

    /**
     * @param bool $sale
     * @param array $price
     * @return array[]
     */
    private function generateSalePriceFilterScript(bool $sale, array $price = []): array
    {
        $filter = "double price; double websitePrice; double salesPrice;
if(doc['websitePrice'].size() > 0){ price = websitePrice = doc['websitePrice'].value; }
if(doc['salesPrice'].size() > 0){ salesPrice = doc['salesPrice'].value; }
if(0 < salesPrice && salesPrice < price) {price = salesPrice; }
doc['status'].value != 2 && doc['dealer.name'].value != 'Operate Beyond'";

        if ($sale) {
            $filter .= " && salesPrice > 0.0 && salesPrice < websitePrice";
        }
        $salesPriceFilter = "";

        if (!empty($price[0])) {
            $salesPriceFilter .= " && salesPrice > " . $price[0];
            $filter .= " && price  > " . $price[0];
        }

        if (!empty($price[1])) {
            $filter .= " && price < " . $price[1];
            $salesPriceFilter .= " && salesPrice < " . $price[1];
        }

        $filter .= $salesPriceFilter;

        return [
            'script' => [
                'source' => $filter,
                'lang' => 'painless'
            ]
        ];
    }

    private function buildBooleanQueryForGlobalFilter(string $name, array $values): array
    {
        $field = str_replace('_', '.', $name);

        $query['bool']['must'][] = [
            'term' => [
                $field => (bool)$values[0]
            ]
        ];

        return $query;
    }

    public function globalQuery(): array
    {
        $this->field->getTerms()->each(function (Term $term) {
            $name = $this->field->getName();
            $values = $term->getValues();

            switch ($name) {
                case 'location_region':
                case 'location_city':
                case 'location_country':
                    $this->appendToQuery($this->buildLocationQueryForGlobalFilter($name, $values));
                    break;
                case 'isArchived':
                case 'showOnWebsite':
                    $this->appendToQuery($this->buildBooleanQueryForGlobalFilter($name, $values));
                    break;
            }

        });

        return $this->query;
    }

    public function buildExcludeIdsQuery(array $data): array {
        return [
            'query' => [
                'bool' => [
                    'must_not' => [
                        [
                            'ids' => [
                                'values' => $data
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function generalQuery(): array
    {
        $this->field->getTerms()->each(function (Term $term) {
            $name = $this->field->getName();
            $values = $term->getValues();
            switch ($name) {
                case 'show_images':
                    $this->appendToQuery($this->buildImagesQuery($values));
                    break;
                case 'clearance_special':
                    $this->appendToQuery($this->buildClearanceSpecialQuery());
                    break;
                case 'location_region':
                case 'location_city':
                case 'location_country':
                    $this->appendToQuery($this->buildLocationQuery($name, $values));
                    break;
                case 'classifieds_site':
                    $this->appendToQuery($this->buildClassifiedsSiteQuery($values[0]));
                    break;
                case 'sale_price_script':
                    $this->appendToQuery($this->buildSalePriceFilterScriptQuery($values));
                    break;
                case 'empty_images':
                    $this->appendToQuery($this->buildEmptyImagesQuery());
                    break;
                case 'availability':
                    $this->appendToQuery($this->buildAvailabilityQuery($term->getOperator(), $values));
                    break;
                case 'excludeIds':
                    $this->appendToQuery($this->buildExcludeIdsQuery($values));
                    break;
            }
        });

        return $this->query;
    }

    /**
     * @param array $query
     * @return void
     */
    private function appendToQuery(array $query)
    {
        $this->query = array_merge_recursive($this->query, $query);
    }
}
