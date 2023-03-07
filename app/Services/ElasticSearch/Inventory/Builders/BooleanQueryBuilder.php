<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Services\ElasticSearch\Inventory\Parameters\Filters\Filter;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Term;

class BooleanQueryBuilder implements FieldQueryBuilderInterface
{
    /** @var Filter */
    private $field;

    /** @var array */
    private $query = [];

    public function __construct(Filter $field)
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
            $termQuery = [
                'term' => [
                    $name => $term->getValues()[0]
                ]
            ];

            if ($term->operatorIsNotEquals()) {
                $termQuery = [
                    'bool' => [
                        $term->getESOperatorKeyword() => [$termQuery]
                    ]
                ];
            }

            $this->appendToQuery($termQuery);
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
