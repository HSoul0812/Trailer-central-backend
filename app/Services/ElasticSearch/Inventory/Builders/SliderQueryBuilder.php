<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

/**
 * Builds a proper ES query for a range, they should be provided as follows:
 *   - existingPrice=7000: which is a range between 7000 and infinite
 *   - existingPrice=:9000 which is a range between 0 and 900
 *   - existingPrice=7000:9000 which is a range between 7000 and 9000
 */
class SliderQueryBuilder implements FieldQueryBuilderInterface
{
    private const DELIMITER = ':';

    /** @var float */
    private $min;

    /** @var float */
    private $max;

    /** @var string */
    private $field;

    public function __construct(string $field, string $data)
    {
        $this->field = $field;
        $parts = explode(self::DELIMITER, $data);

        $this->min = $parts[0] ?? 0;
        $this->max = $parts[1] ?? 0;
    }

    public function query(): array
    {
        $range = $this->getRangeTerm();

        switch ($this->field) {
            // if we would need to handle edges cases, then we need to handle here
            default:
                $boolQuery = [
                    'bool' => [
                        'filter' => [
                            [
                                'range' => [
                                    $this->field => $range
                                ]
                            ]
                        ]
                    ]
                ];

                return [
                    'post_filter' => $boolQuery,
                    'aggregations' => [
                        'filter_aggregations' => ['filter' => $boolQuery],
                        'selected_location_aggregations' => ['filter' => $boolQuery]
                    ]
                ];
        }
    }

    public function getRangeTerm(): array
    {
        $range = [];

        if ($this->min) {
            $range['gte'] = $this->min;
        }

        if ($this->max) {
            $range['lte'] = $this->max;
        }

        return $range;
    }
}
