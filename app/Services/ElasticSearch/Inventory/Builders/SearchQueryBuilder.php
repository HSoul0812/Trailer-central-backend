<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

use App\Services\ElasticSearch\Inventory\Parameters\Filters\Filter;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Term;
use Illuminate\Support\Arr;

/**
 * Please, any change in this handler should be reviewed in a console to check query performance
 */
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
        'title' => ['title^4', 'title.tokens^4'],
        'description' => 'description.tokens^1',
        'stock' => 'stock^1',
        'vin' => 'vin^1',
        'manufacturer' => 'manufacturer^1',
        'brand' => 'brand^1',
        'model' => 'model^1',
        'featureList.floorPlan' => 'featureList.floorPlan.tokens^0.5',
    ];

    /** @var string[] */
    private const DESCRIPTION_SEARCH_FIELDS = self::SEARCH_FIELDS;

    /** @var array */
    private $query = [];

    public function __construct(Filter $field)
    {
        $this->field = $field;
    }

    /**
     * @param string|array $fields
     * @param string $termAsString
     * @return \array[][]
     */
    private function queryStringWithBoost($fields, string $termAsString): array
    {
        if (!is_array($fields)) {
            $fields = [$fields];
        }

        // to be able quoting special chars
        $terms = array_map(static function ($term): string {
            return preg_replace(
                "/[\\+\\-\\=\\&\\|\\!\\(\\)\\{\\}\\[\\]\\^\\\"\\~\\*\\<\\>\\?\\:\\\\\\/]/",
                addslashes('\\$0'),
                $term
            );
        },
            array_filter(explode(' ', strtolower(trim($termAsString))))
        );

        $numberOfTerms = count($terms);

        if($numberOfTerms === 0) {
            return [];
        }

        $query = sprintf('*%s*', $terms[0]);

        if ($numberOfTerms > 1) {
            $queries = [
                sprintf('*%s', $terms[0])
            ];

            array_shift($terms);

            $lastQuery = sprintf('%s*', $terms[$numberOfTerms - 2]);

            array_pop($terms);

            foreach ($terms as $term) {
                $queries[] = $term;
            }

            $queries[] = $lastQuery;

            $query = implode(' AND ', $queries);
        }

        return [
            'query_string' => [
                'fields' => $fields,
                'query' => $query
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
        $this->field->getTerms()->each(function (Term $term) {
            $name = $this->field->getName();

            $operator = $this->field->getParentESOperatorKeyword() === 'must' && $term->getESOperatorKeyword() === 'should' ?
                'must': $term->getESOperatorKeyword();

            $boolQuery = [
                'bool' => [
                    $operator =>[]
                ]
            ];

            foreach (array_filter($term->getValues()) as $value) {
                // we're assuming all fields are analyzed with `shingle_analyzer`
                $boolQuery['bool'][$operator][] = $this->queryStringWithBoost(
                    self::SEARCH_FIELDS[$name] ?? sprintf('%s.tokens', $name),
                    $value
                );
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
        $this->field->getTerms()->each(function (Term $term) {
            $shouldQuery = [];
            $name = $this->field->getName();
            $data = $term->getValues();

            switch ($name) {
                case 'stock':
                    $shouldQuery[] = $this->queryStringWithBoost(self::SEARCH_FIELDS['stock'], $data['match']);
                    break;
                default:
                    $searchFields = Arr::flatten($this->getSearchFields($data['ignore_fields'] ?? []));

                    $shouldQuery[] = $this->queryStringWithBoost($searchFields, $data['match']);
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
