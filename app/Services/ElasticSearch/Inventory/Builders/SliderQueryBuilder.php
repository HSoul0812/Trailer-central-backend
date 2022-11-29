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
        $this->field->getTerms()->each(function (Term $term) {
            $this->appendToQuery([
                [
                    'bool' => [
                        $term->getESOperatorKeyword() => [
                            [
                                'range' => [
                                    $this->field->getName() => $term->getValues()
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
