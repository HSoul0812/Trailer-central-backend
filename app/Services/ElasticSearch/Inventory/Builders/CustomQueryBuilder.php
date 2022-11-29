<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Models\Inventory\Inventory;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Field;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Term;

class CustomQueryBuilder implements FieldQueryBuilderInterface
{
    /**
     * @var string
     */
    private $field;

    /** @var array */
    private $query = [];

    /**
     * @param Field $field
     */
    public function __construct(Field $field)
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
                                            'title' => [
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

    private function buildClassifiedsSiteQuery(): array
    {
        // when it is not a classifieds site then it should filter by `isArchived` & `isArchived` & `status`
        return [
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
    }

    /**
     * @return array|string[][][][][][]
     */
    private function buildEmptyImagesQuery(): array
    {
        return [
            'query' => [
                'bool' => [
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
        return [
            'query' => [
                'bool' => [
                    $operator == Term::OPERATOR_EQ ? 'must' : 'must_not' => [
                        [
                            'term' => [
                                'availability' => $values[0]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return \array[][][][][]
     */
    private function buildRentalBoolQuery(array $rental): array
    {
        return [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'isRental' => $rental[0]
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

        if (count($price)) {
            $filter .= " && price  > " . $price[0] . "&& price < " . $price[1];
        }

        return [
            'script' => [
                'source' => $filter,
                'lang' => 'painless'
            ]
        ];
    }

    public function globalQuery(): array
    {
        return $this->query;
    }

    public function generalQuery(): array
    {
        $this->field->getTerms()->each(function (Term $term) {
            $name = $this->field->getName();
            $values = $term->getValues();
            switch ($name) {
                case 'show_images':
                    return $this->buildImagesQuery($values);
                case 'clearance_special':
                    return $this->buildClearanceSpecialQuery();
                case 'location_region':
                case 'location_city':
                case 'location_country':
                    return $this->buildLocationQuery($name, $values);
                case 'classifieds_site':
                    return $this->buildClassifiedsSiteQuery();
                case 'sale_price_script':
                    return $this->buildSalePriceFilterScriptQuery($values);
                case 'empty_images':
                    return $this->buildEmptyImagesQuery();
                case 'availability':
                    return $this->buildAvailabilityQuery($term->getOperator(), $values);
                case 'rental_bool':
                    return $this->buildRentalBoolQuery($values);
                default:
                    return [];
            }
        });

        return $this->query;
    }
}
