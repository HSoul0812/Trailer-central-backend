<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

class BooleanQueryBuilder implements FieldQueryBuilderInterface
{
    /** @var string */
    private $field;

    /** @var bool */
    private $value;

    public function __construct(string $field, string $data)
    {
        $this->field = $field;
        $this->value = boolval($data);
    }


    public function query(): array
    {
        $boolQuery = [
            'bool' => [
                'filter' => [
                    [
                        'terms' => [
                            $this->field => [$this->value]
                        ]
                    ]
                ]
            ]
        ];
        switch ($this->field) {
            // if we would need to handle edges cases, then we need to handle here
            default:
                return [
                    'post_filter' => $boolQuery,
                    'aggregations' => [
                        'filter_aggregations' => ['filter' => $boolQuery],
                        'location_aggregations' => ['filter' => $boolQuery]
                    ]
                ];
        }
    }
}
