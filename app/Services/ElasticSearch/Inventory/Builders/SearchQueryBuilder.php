<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Services\ElasticSearch\Inventory\Parameters\Filters\Filter;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Term;

class SearchQueryBuilder implements FieldQueryBuilderInterface
{
    /** @var string */
    private const DEFAULT_BOOST = 1;

    /** @var string */
    private const MINIMUM_BOOST = 0.001;

    /** @var Filter */
    private $field;

    /** @var string[] */
    private const SEARCH_FIELDS = [
        'title' => 'title.txt^4',
        'description' => 'description.txt^1',
        'stock' => 'stock.normal^1',
        'vin' => 'vin^1',
        'manufacturer' => 'manufacturer^1',
        'brand' => 'brand^1',
        'model' => 'model^1',
        'featureList.floorPlan' => 'featureList.floorPlan.txt^0.5',
    ];

    /** @var string[] */
    private const DESCRIPTION_SEARCH_FIELDS = [
        'title' => 'title.txt^4',
        'manufacturer' => 'manufacturer^1',
        'brand' => 'brand^1',
        'description' => 'description.txt^1',
        'stock' => 'stock.normal^1',
        'model' => 'model^1',
        'vin' => 'vin^1',
        'featureList.floorPlan' => 'featureList.floorPlan.txt^0.5',
    ];

    /** @var array */
    private $query = [];

    public function __construct(Filter $field)
    {
        $this->field = $field;
    }

    /**
     * @param string $value
     * @return \array[][]
     */
    private function wildcardQuery(string $value): array
    {
        return [
            'wildcard' => [
                $this->field->getName() => [
                    'value' => sprintf('*%s*', $value)
                ]
            ]
        ];
    }

    /**
     * @param string $column
     * @param float $boost
     * @param string $value
     * @return \array[][]
     */
    private function wildcardQueryWithBoost(string $column, float $boost, string $value): array
    {
        return [
            'wildcard' => [
                $column => [
                    'value' => sprintf('*%s*', str_replace(' ', '*', $value)),
                    'boost' => max(self::MINIMUM_BOOST, $boost - 0.95)
                ]
            ]
        ];
    }

    /**
     * @param string $field
     * @param float $boost
     * @param string $value
     * @return \array[][]
     */
    private function matchQuery(string $field, float $boost, string $value): array
    {
        return [
            'match' => [
                $field => [
                    'query' => $value,
                    'operator' => 'and',
                    'boost' => $boost
                ]
            ]
        ];
    }

    /**
     * @param array $ignoreList
     * @return string[]
     */
    private function getSearchFields(array $ignoreList): array
    {
        $name = $this->field->getName();

        if ($name === 'description') {
            $descriptionSearchFields = self::DESCRIPTION_SEARCH_FIELDS;

            foreach ($ignoreList as $ignore) {
                unset($descriptionSearchFields[$ignore]);
            }

            return $descriptionSearchFields;
        }

        if (isset(self::SEARCH_FIELDS[$name])) {
            return [self::SEARCH_FIELDS[$name]];
        }

        return self::SEARCH_FIELDS;
    }

    /**
     * @return array
     */
    public function globalQuery(): array
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function generalQuery(): array
    {
        $this->field->getTerms()->each(function (Term $term) {
            $shouldQuery = [];
            $name = $this->field->getName();
            $data = $term->getValues();
            switch ($name) {
                case 'stock':
                    $shouldQuery[] = $this->wildcardQuery($data['match']);
                    break;
                default:
                    $searchFields = $this->getSearchFields($data['ignore_fields'] ?? []);
                    foreach ($searchFields as $key => $column) {
                        $boost = self::DEFAULT_BOOST;
                        $columnValues = explode('^', $column);

                        if (isset($columnValues[$boostKey = 1])) {
                            $boost = max(self::MINIMUM_BOOST, (float)$columnValues[$boostKey]);
                        }

                        $shouldQuery[] = $this->matchQuery($columnValues[0], $boost, $data['match']);
                        $shouldQuery[] = $this->matchQuery($column, $boost, $data['match']);

                        if (!is_numeric($key) && strpos($column, '.') !== false) {
                            $shouldQuery[] = $this->wildcardQueryWithBoost($key, $boost, $data['match']);
                        }
                    }
                    break;
            }

            $searchQuery = [
                'bool' => [
                    'filter' => [
                        [
                            'bool' => [
                                'should' => $shouldQuery
                            ]
                        ]
                    ]
                ]
            ];

            $this->appendToQuery([
                'post_filter' => $searchQuery,
                'aggregations' => [
                    'filter_aggregations' => ['filter' => $searchQuery],
                    'selected_location_aggregations' => ['filter' => $searchQuery]
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
