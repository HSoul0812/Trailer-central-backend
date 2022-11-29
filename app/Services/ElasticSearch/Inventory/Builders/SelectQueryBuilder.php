<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Services\ElasticSearch\Inventory\Parameters\Filters\Term;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Filter;
use App\Services\ElasticSearch\Inventory\Parameters\DealerLocationIds;

class SelectQueryBuilder implements FieldQueryBuilderInterface
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
            $name = $this->field->getName();
            $options = $term->getValues();
            $queries = [];

            if ($name === 'dealerLocationId') {
                $options = DealerLocationIds::fromArray($options);
            }

            if ($name == 'dealerLocationId' && $options->isFilterable()) {
                $options = $options->locations();
            }

            if ($name == 'isRental') {
                $options = array_map('boolval', $options);
            }

            foreach ($options as $value) {
                $queries[] = [
                    'term' => [
                        $name => $value
                    ]
                ];
            }
            $this->appendToQuery([
                [
                    'bool' => [
                        $term->getESOperatorKeyword() => $queries
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
            $options = $term->getValues();

            switch ($name) {
                case 'dealerLocationId':
                    $options = DealerLocationIds::fromArray($options);
                    $optionsQuery = [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => [
                                        $name => $options->locations()
                                    ]
                                ]
                            ]
                        ]
                    ];
                    $this->appendToQuery([
                        'post_filter' => $options->isFilterable() ? $optionsQuery : [],
                        'sort' => [
                            [
                                '_script' => [
                                    'type' => 'number',
                                    'script' => [
                                        'inline' => "
                                    if(doc['dealerLocationId'].value != null) {
                                        for(int i=0; i < params['locations'].length; i++) {
                                            if(params['locations'][i] == doc['dealerLocationId'].value) return -1;
                                        }
                                        return 0;
                                    } else { return 1; }",
                                        'params' => [
                                            'locations' => array_map(static function ($location) {
                                                return (int)$location;
                                            }, $options->locationsForSubAggregatorsFiltering())
                                        ]
                                    ],
                                    'order' => 'asc'
                                ]
                            ]
                        ],
                        'aggregations' => [
                            'filter_aggregations' => $options->isFilterable() ? ['filter' => $optionsQuery] : [],
                            'selected_location_aggregations' => ['filter' => [
                                'bool' => [
                                    'filter' => [
                                        [
                                            'terms' => [
                                                $name => $options->locationsForSubAggregatorsFiltering()
                                            ]
                                        ]
                                    ]
                                ]
                            ]]
                        ]
                    ]);
                    break;
                case 'isRental':
                    $options = array_map('boolval', $options);
                default:
                    $optionsQuery = [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => [
                                        $name => $options
                                    ]
                                ]
                            ]
                        ]
                    ];
                    $this->appendToQuery([
                        'post_filter' => $optionsQuery,
                        'aggregations' => [
                            'filter_aggregations' => ['filter' => $optionsQuery],
                            'selected_location_aggregations' => ['filter' => $optionsQuery]
                        ]
                    ]);
            }
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
