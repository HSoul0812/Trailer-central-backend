<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

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
    private const IMAGES_DELIMETER = ';';

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
                                }, explode(self::IMAGES_DELIMETER, $this->data))
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
}
