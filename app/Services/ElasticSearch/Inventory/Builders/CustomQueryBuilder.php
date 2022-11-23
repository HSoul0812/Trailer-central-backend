<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Models\Inventory\Inventory;
use Illuminate\Support\Str;

/**
 * Builds a proper ES query for a custom fields & edge cases
 *   - show_images=jpg;png: which is *.jpg & *.png
 *   - clearance_special=1
 */
class CustomQueryBuilder implements FieldQueryBuilderInterface
{
    /**
     * @var string
     */
    private $field;
    /**
     * @var string
     */
    private $data;

    /** @var string */
    private const DELIMITER = ';';

    /** @var string */
    private const DELIMITER_EXCLUSION = '~';

    /** @var string */
    private const SALE_SCRIPT_ATTRIBUTE = 'sale_script';

    /** @var string */
    private const PRICE_SCRIPT_ATTRIBUTE = 'price_script';

    /**
     * @param string $field
     * @param string $data
     */
    public function __construct(string $field, string $data)
    {
        $this->field = $field;
        $this->data = $data;
    }

    public function query(): array
    {
        switch ($this->field) {
            case 'show_images':
                return $this->buildImagesQuery();
            case 'clearance_special':
                return $this->buildClearanceSpecialQuery();
            case 'location_region':
            case 'location_city':
            case 'location_country':
                return $this->buildLocationQuery();
            case 'classifieds_site':
                return $this->buildClassifiedsSiteQuery();
            case 'sale_price_script':
                return $this->buildSalePriceFilterScriptQuery();
            case 'empty_images':
                return $this->buildEmptyImagesQuery();
            case 'availability':
                return $this->buildAvailabilityQuery();
            case 'rental_bool':
                return $this->buildRentalBoolQuery();
            default:
                return [];
        }
    }

    /**
     * @return \array[][][][][]
     */
    private function buildImagesQuery(): array
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
                                }, explode(self::DELIMITER, $this->data))
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
        if ($this->data) {
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

        return [];
    }

    private function buildLocationQuery(): array
    {
        $field = str_replace('_', '.', $this->field);

        $query = [
            'bool' => [
                'must' => [
                    [
                        'term' => [
                            $field => $this->data
                        ]
                    ]
                ]
            ]
        ];

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
        return $this->data ? [] : [
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
     * @return array|\string[][][][][][]
     */
    private function buildEmptyImagesQuery(): array
    {
        return !boolval($this->data) ? [
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
        ] : [];
    }

    /**
     * @return \string[][][][][][]
     */
    private function buildAvailabilityQuery(): array
    {
        $value = $this->data;
        $query = 'must';

        if (Str::startsWith($value, self::DELIMITER_EXCLUSION)) {
            $query = 'must_not';
            $value = substr($value, 1);
        }

        return [
            'query' => [
                'bool' => [
                    $query => [
                        [
                            'term' => [
                                'availability' => $value
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
    private function buildRentalBoolQuery(): array
    {
        return [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'isRental' => boolval($this->data)
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
    private function buildSalePriceFilterScriptQuery(): array
    {
        $values = explode(self::DELIMITER, $this->data);

        $sale = in_array(self::SALE_SCRIPT_ATTRIBUTE, $values);
        $price = $this->getPriceForFilterScript($values);

        return [
            'query' => [
                'bool' => [
                    'filter' => [
                        'script' => $this->generateSalePriceFilterScript($sale, $price)
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

    /**
     * @param array $values
     * @return array
     */
    private function getPriceForFilterScript(array $values): array
    {
        foreach ($values as $value) {
            if (Str::startsWith($value, self::PRICE_SCRIPT_ATTRIBUTE)) {
                $priceParts = explode(':', $value);
                return [$priceParts[1], $priceParts[2]];
            }
        }
        return [];
    }
}
