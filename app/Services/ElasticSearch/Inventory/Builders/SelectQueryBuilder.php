<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Services\ElasticSearch\Inventory\Parameters\DealerLocationIds;

/**
 * Builds a proper ES query for a select, they should be provided as follows:
 *   - category=camping_rv;fifth_wheel_campers: which is a category with options camping_rv & fifth_wheel_campers
 */
class SelectQueryBuilder implements FieldQueryBuilderInterface
{
    public const DELIMITER = ';';

    /** @var string */
    private $field;

    /** @var array|DealerLocationIds */
    private $options;

    public function __construct(string $field, string $data)
    {
        $this->field = $field;

        $this->options = explode(self::DELIMITER, $data);

        if ($this->field === 'dealerLocationId') {
            $this->options = DealerLocationIds::fromString($data);
        }
    }

    public function query(): array
    {
        switch ($this->field) {
            case 'dealerLocationId':
                $optionsQuery = [
                    'bool' => [
                        'filter' => [
                            [
                                'terms' => [
                                    $this->field => $this->options->locations()
                                ]
                            ]
                        ]
                    ]
                ];

                return [
                    'post_filter' => $optionsQuery,
                    'aggregations' => [
                        'filter_aggregations' => ['filter' => $optionsQuery],
                        'selected_location_aggregations' => ['filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'terms' => [
                                            $this->field => $this->options->locationsForSubAggregatorsFiltering()
                                        ]
                                    ]
                                ]
                            ]
                        ]]
                    ]
                ];
            default:
                $optionsQuery = [
                    'bool' => [
                        'filter' => [
                            [
                                'terms' => [
                                    $this->field => $this->options
                                ]
                            ]
                        ]
                    ]
                ];

                return [
                    'post_filter' => $optionsQuery,
                    'aggregations' => [
                        'filter_aggregations' => ['filter' => $optionsQuery],
                        'selected_location_aggregations' => ['filter' => $optionsQuery]
                    ]
                ];
        }
    }
}
