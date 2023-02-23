<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Repositories\FeatureFlagRepositoryInterface;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Filter;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Term;

class SearchQueryBuilder implements FieldQueryBuilderInterface
{
    /** @var int */
    private const DEFAULT_BOOST = 1;

    /** @var int */
    private const GLOBAL_FILTER_WILDCARD_BOOST = 4;

    /** @var float */
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
     * @param string $column
     * @param float $boost
     * @param string $value
     * @return \array[][]
     */
    private function wildcardQueryWithBoost(string $column, float $boost, string $value): array
    {
        $termParts = explode(' ', $value);
        // Given full search over tokens are faster than over keyword fields, so, we need always search using then,
        // only exception is when the number of term words are greater than 4,
        // in that particular case we need to use the keyword field itself
        $column = count($termParts) > 4 ? $column : $column.'.tokens';

        return [
            'wildcard' => [
                $column => [
                    'value' => sprintf('*%s*', $value),
                    'boost' => max(self::MINIMUM_BOOST, $boost)
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
     * @param string[] $fields
     * @param float $boost
     * @param string $value
     * @return \array[][]
     */
    private function multiMatchQuery(array $fields, float $boost, string $value): array
    {
        return [
            'multi_match' => [
                'query' => $value,
                'operator' => 'and',
                'boost' => $boost,
                'fields' => $fields
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

            $operator = $this->field->getParentESOperatorKeyword() === 'must' && $term->getESOperatorKeyword() === 'should' ?
                'must': $term->getESOperatorKeyword();

            $boolQuery = [
                'bool' => [
                    $operator =>[]
                ]
            ];

            foreach ($term->getValues() as $value) {

                $boolQuery['bool'][$operator][] =  $this->matchQuery(
                    sprintf('%s.txt', $name),
                    self::GLOBAL_FILTER_WILDCARD_BOOST + self::DEFAULT_BOOST,
                    $value
                );

                if ($name !== 'description' || $descriptionWildcard) {
                    $boolQuery['bool'][$operator][]  = $this->wildcardQueryWithBoost(
                        $name,
                        self::GLOBAL_FILTER_WILDCARD_BOOST,
                        $value
                    );
                }
            }

             $this->appendToQuery($boolQuery);
        });

        return $this->query;
    }

    /**
     * @return array
     */
    public function generalQuery(): array
    {
        $keywordWildcard = app(FeatureFlagRepositoryInterface::class)->isEnabled('inventory-sdk-es-keyword-wildcard');

        $this->field->getTerms()->each(function (Term $term) use ($keywordWildcard) {
            $shouldQuery = [];
            $name = $this->field->getName();
            $data = $term->getValues();

            switch ($name) {
                case 'stock':
                    $shouldQuery[] = $this->wildcardQueryWithBoost(
                        $this->field->getName(),
                        self::DEFAULT_BOOST,
                        $data['match']
                    );
                    break;
                default:
                    $searchFields = $this->getSearchFields($data['ignore_fields'] ?? []);
                    $match = str_replace(' ', '*', $data['match']);

                    foreach ($searchFields as $key => $column) {
                        $boost = self::DEFAULT_BOOST;
                        $columnValues = explode('^', $column);

                        if (isset($columnValues[$boostKey = 1])) {
                            $boost = max(self::MINIMUM_BOOST, (float)$columnValues[$boostKey]);
                        }

                        $columnParts = explode('.', $column);

                        if (count($columnParts) > 1) {
                            $shouldQuery[] = $this->multiMatchQuery(
                                [$columnParts[0], $columnValues[0]],
                                $boost,
                                $match
                            );
                        } else {
                            $shouldQuery[] = $this->matchQuery($columnValues[0], $boost, $match);
                        }

                        if ($keywordWildcard && !is_numeric($key) && strpos($column, '.') !== false) {
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
    private function appendToQuery(array $query): void
    {
        $this->query = array_merge_recursive($this->query, $query);
    }
}
