<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

/**
 * Builds a proper ES query for a select, they should be provided as follows:
 *   - category=camping_rv;fifth_wheel_campers: which is a category with options camping_rv & fifth_wheel_campers
 */
class SelectQueryBuilder implements FieldQueryBuilderInterface
{
    public const DELIMITER = ';';

    /** @var string */
    private $field;

    /** @var array */
    private $options;

    public function __construct(string $field, string $data)
    {
        $this->field = $field;
        $this->options = explode(self::DELIMITER, $data);
    }

    public function query(): array
    {
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

        switch ($this->field) {
            // if we would need to handle edges cases, then we need to handle here
            default:
                // almost any `post_filter` should also append to `filter_aggregations` and `selected_location_aggregations`
                // but there and edge case when the selected locations (post_filter) could be different for `selected_location_aggregations`
                // that's the nationwide case for Bishs
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
