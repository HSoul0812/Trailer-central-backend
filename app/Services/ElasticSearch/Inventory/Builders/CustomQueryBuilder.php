<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Models\Inventory\Inventory;

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
    private const IMAGES_DELIMITER = ';';

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
                return $this->buildLocationQuery();
            case 'classifieds_site':
                return $this->buildClassifiedsSiteQuery();
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
                                }, explode(self::IMAGES_DELIMITER, $this->data))
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
                'filter_aggregations' => [
                    'filter' => $query
                ]
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
}
