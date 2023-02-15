<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Repositories\FeatureFlagRepositoryInterface;
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

    /** @var string[] */
    private const REPLACE_SPACE_WITH_ASTERISK = [
        'manufacturer',
        'brand',
        'description.txt',
        'stock.normal',
        'model',
        'vin',
        'featureList.floorPlan.txt'
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
                    'value' => $value
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
                    'value' => $value,
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
            return [$name => self::SEARCH_FIELDS[$name]];
        }

        return self::SEARCH_FIELDS;
    }

    /**
     * @return array
     */
    public function globalQuery(): array
    {
        $descriptionWildcard = app(FeatureFlagRepositoryInterface::class)->isEnabled('inventory-sdk-global-description-wildcard');

        $this->field->getTerms()->each(function (Term $term) use ($descriptionWildcard) {
            $name = $this->field->getName();

            $query = [
                'bool' => [
                    'must' => array_map(static function ($value) use ($term, $name, $descriptionWildcard) {
                        $searchQuery = [
                            [
                                'match' => [
                                    sprintf('%s.txt', $name) => [
                                        'query' => $value,
                                        'operator' => 'and'
                                    ]
                                ]
                            ]
                        ];

                        if ($name !== 'description' || $descriptionWildcard) {
                            $searchQuery[] = [
                                'wildcard' => [
                                    $name => [
                                        'value' => sprintf('*%s', $value)
                                    ]
                                ]
                            ];

                            $searchQuery[] = [
                                'wildcard' => [
                                    $name => [
                                        'value' => sprintf('%s*', $value)
                                    ]
                                ]
                            ];
                        }

                        return [
                            'bool' => [
                                $term->getESOperatorKeyword() => $searchQuery
                            ]
                        ];
                    }, $term->getValues())
                ]
            ];

            $this->appendToQuery($query);
        });

        return $this->query;
    }

    /**
     * @return array
     */
    public function generalQuery(): array
    {
        // @todo: remove keyword-wildcard feature when safe
        $keywordWildcard = app(FeatureFlagRepositoryInterface::class)->isEnabled('inventory-sdk-keyword-wildcard');

        $this->field->getTerms()->each(function (Term $term) use ($keywordWildcard) {
            $shouldQuery = [];
            $name = $this->field->getName();
            $data = $term->getValues();

            switch ($name) {
                case 'stock':
                    $shouldQuery[] = $this->wildcardQuery(sprintf('*%s', $data['match']));
                    $shouldQuery[] = $this->wildcardQuery(sprintf('%s*', $data['match']));
                    break;
                default:
                    $searchFields = $this->getSearchFields($data['ignore_fields'] ?? []);

                    foreach ($searchFields as $key => $column) {
                        $boost = self::DEFAULT_BOOST;
                        $columnValues = explode('^', $column);

                        if (isset($columnValues[$boostKey = 1])) {
                            $boost = max(self::MINIMUM_BOOST, (float)$columnValues[$boostKey]);
                        }

                        $match = $data['match'];
                        if (in_array($columnValues[0], self::REPLACE_SPACE_WITH_ASTERISK)) {
                            $match = str_replace(' ', '*', $match);
                        }

                        $shouldQuery[] = $this->matchQuery($columnValues[0], $boost, $match);
                        $shouldQuery[] = $this->matchQuery($column, $boost, $match);

                        if ($keywordWildcard && !is_numeric($key) && strpos($column, '.') !== false) {
                            $shouldQuery[] = $this->wildcardQueryWithBoost($key, $boost, sprintf('*%s', str_replace(' ', '*', $data['match'])));
                            $shouldQuery[] = $this->wildcardQueryWithBoost($key, $boost, sprintf('%s*', str_replace(' ', '*', $data['match'])));
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
