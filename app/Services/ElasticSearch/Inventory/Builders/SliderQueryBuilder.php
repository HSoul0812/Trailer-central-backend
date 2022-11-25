<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Services\ElasticSearch\Inventory\Parameters\Filters\Field;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Term;

class SliderQueryBuilder implements FieldQueryBuilderInterface
{
    /** @var Field */
    private $field;

    /** @var array */
    private $query = [];

    public function __construct(Field $field)
    {
        $this->field = $field;
    }

    /**
     * @return array
     */
    public function globalQuery(): array
    {
        $this->field->getTerms()->each(function (Term $term) {
            $this->appendToQuery([
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'bool' => [
                                    $term->getESOperatorKeyword() => [
                                        [
                                            'range' => [
                                                $this->field->getName() => $term->getValues()[0]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
        });
        return $this->query;
    }

    /**
     * @return array
     */
    public function generalQuery(): array
    {
        $this->field->getTerms()->each(function (Term $term) {
            $boolQuery = [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                $this->field->getName() => $term->getValues()[0]
                            ]
                        ]
                    ]
                ]
            ];
            $this->appendToQuery([
                'post_filter' => $boolQuery,
                'aggregations' => [
                    'filter_aggregations' => ['filter' => $boolQuery],
                    'selected_location_aggregations' => ['filter' => $boolQuery]
                ]
            ]);
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
