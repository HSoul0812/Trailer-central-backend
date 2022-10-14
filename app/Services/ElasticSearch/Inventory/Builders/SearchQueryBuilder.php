<?php

namespace App\Services\ElasticSearch\Inventory\Builders;

/**
 * Builds a proper ES query for a search by keyword, they should be provided as follows:
 *   - keyword=some text to search
 *   - keyword=description|some text to search: where `description` is ignored from the search list
 *   - keyword=description|model|some text to search: ignore description & model
 */
class SearchQueryBuilder implements FieldQueryBuilderInterface
{
    /** @var string */
    private const DELIMETER = '|';

    /** @var string */
    private $field;

    /** @var string */
    private $value;

    /** @var string[] */
    private $ignore;

    /** @var string[] */
    private const SEARCH_FIELDS = [
        'title' => 'title.txt',
        'description' => 'description.txt',
        'stock' => 'stock.normal',
        'vin' => 'vin',
        'manufacturer' => 'manufacturer',
        'brand' => 'brand',
        'model' => 'model.txt',
        'featureList.floorPlan' => 'featureList.floorPlan.txt',
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

    /** @var string */
    private const INCLUDE_DESCRIPTION_ON_SEARCH_CONFIG_KEY = 'inventory/include_description_on_search';

    public function __construct(string $field, string $data)
    {
        $this->field = $field;
        $keywordParts = explode(self::DELIMETER, $data);
        $this->value = array_pop($keywordParts);
        $this->ignore = $keywordParts;
    }

    public function query(): array
    {
        $shouldQuery = [];

        switch ($this->field) {
            case 'stock':
                $shouldQuery[] = $this->wildcardQuery();
                break;
            default:
                $columnsToSearch = $this->getSearchFields();
                foreach ($columnsToSearch as $key => $column) {
                    $boost = 1;
                    $columnValues = explode('^', $column);
                    if (isset($columnValues[$boostKey = 1])) {
                        $boost = floatval($columnValues[$boostKey]);
                    }
                    $shouldQuery[] = $this->matchQuery($columnValues[0], $boost);
                    $shouldQuery[] = $this->matchQuery($column, $boost);

                    if (!is_numeric($key) && strpos($column, '.') !== false) {
                        $shouldQuery[] = $this->wildcardQueryWithBoost($key, $boost);
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

        return [
            'post_filter' => $searchQuery,
            'aggregations' => [
                'filter_aggregations' => ['filter' => $searchQuery],
                'location_aggregations' => ['filter' => $searchQuery]
            ]
        ];
    }

    private function wildcardQuery(): array
    {
        return [
            'wildcard' => [
                $this->field => [
                    'value' => sprintf('*%s*', $this->value)
                ]
            ]
        ];
    }

    private function wildcardQueryWithBoost(string $column, float $boost): array
    {
        return [
            'wildcard' => [
                $column => [
                    'value' => sprintf('*%s*', str_replace(' ', '*', $this->value)),
                    'boost' => max(0, $boost - 0.95)
                ]
            ]
        ];
    }

    private function matchQuery(string $field, float $boost): array
    {
        return [
            "match" => [
                $field => [
                    "query" => $this->value,
                    "operator" => "and",
                    "boost" => $boost
                ]
            ]
        ];
    }

    private function getSearchFields(): array
    {
        if ($this->field == 'description') {
            $descriptionSearchFields = self::DESCRIPTION_SEARCH_FIELDS;
            foreach ($this->ignore as $ignore) {
                unset($descriptionSearchFields[$ignore]);
            }
            return $descriptionSearchFields;
        } else if (isset(self::SEARCH_FIELDS[$this->field])) {
            return [self::SEARCH_FIELDS[$this->field]];
        }
        return self::SEARCH_FIELDS;
    }
}
