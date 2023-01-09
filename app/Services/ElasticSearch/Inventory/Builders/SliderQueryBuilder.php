<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Services\ElasticSearch\Inventory\Parameters\Filters\Filter;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Term;

class SliderQueryBuilder implements FieldQueryBuilderInterface
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
     * @return array
     */
    public function globalQuery(): array
    {
        $this->field->getTerms()->filter(function (Term $term): bool {
            return array_key_exists('lte', $term->getValues()) || array_key_exists('gte', $term->getValues());
        })->each(function (Term $term) {
            $queries = [
                'bool' => [
                    'must' => [
                        [
                            'range' => [
                                $this->field->getName() => $term->getValues()
                            ]
                        ]
                    ]
                ]
            ];

            $this->appendToQuery($queries);
        });

        $this->field->getTerms()->filter(function (Term $term): bool {
            return !(array_key_exists('lte', $term->getValues()) || array_key_exists('gte', $term->getValues()));
        })->each(function (Term $term) {
            $name = $this->field->getName();
            $options = $term->getValues();

            $queries = [
                'bool' => [
                    'must' => [
                        [
                            'terms' => [
                                $name => $options
                            ]
                        ]
                    ]
                ]
            ];

            if ($term->operatorIsNotEquals()) {
                $queries = [
                    'bool' => [
                        $term->getESOperatorKeyword() => $queries
                    ]
                ];
            }

            $this->appendToQuery($queries);
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
                                $this->field->getName() => $term->getValues()
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
