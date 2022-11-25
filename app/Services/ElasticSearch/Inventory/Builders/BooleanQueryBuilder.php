<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Services\ElasticSearch\Inventory\Parameters\Filters\Field;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Term;

class BooleanQueryBuilder implements FieldQueryBuilderInterface
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
     * @param array $query
     * @return void
     */
    private function appendToQuery(array $query)
    {
        $this->query = array_merge_recursive($this->query, $query);
    }

    /**
     * @return array
     */
    public function globalQuery(): array
    {
        $this->field->getTerms()->each(function (Term $term) {
            $name = $this->field->getName();
            $this->appendToQuery([
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'bool' => [
                                    $term->getESOperatorKeyword() => [
                                        [
                                            'term' => [
                                                $name => $term->getValues()[0]
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
            $name = $this->field->getName();
            $boolQuery = [
                'bool' => [
                    'filter' => [
                        [
                            'terms' => [
                                $name => $term->getValues()
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
}
