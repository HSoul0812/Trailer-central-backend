<?php

namespace App\Services\Inventory\ESQuery;

use stdClass;

class ESInventoryQueryBuilder
{
    public const OCCUR_MUST = 'must';
    public const OCCUR_SHOULD = 'should';
    public const OCCUR_MUST_NOT = 'must_not';

    private array $queries = [
        'must' => [],
        'should' => [],
        'must_not' => [],
        'filter' => [],
    ];

    private bool $willPaginate = false;
    private ?int $page = null;
    private ?int $pageSize = null;
    private ?array $globalAggregations = null;
    private ?array $filterAggregations = null;
    private ?array $location = null;
    private ?string $distance = null;
    private ?array $filterScript = null;

    private ?string $orderField = null;
    private ?string $orderDir = null;
    private bool $orderRandom = false;

    public function getWillPaginate(): bool
    {
        return $this->willPaginate;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function getPageSize(): ?int
    {
        return $this->pageSize;
    }

    public function addRangeQuery(string $fieldKey, $min, $max, $context = self::OCCUR_MUST)
    {
        if ($min != null || $max != null) {
            $rangeQuery = [
                'range' => [
                    $fieldKey => [],
                ],
            ];
            if ($min != null) {
                $rangeQuery['range'][$fieldKey]['gt'] = $min;
            }
            if ($max != null) {
                $rangeQuery['range'][$fieldKey]['lt'] = $max;
            }

            $this->queries[$context][] = $rangeQuery;
        }

        return $this;
    }

    public function addExistsQuery(string $fieldKey, $context = self::OCCUR_MUST)
    {
        $query = $this->buildExistsQuery($fieldKey);
        if ($query != null) {
            $this->queries[$context][] = $query;
        }

        return $this;
    }

    public function addTermQuery(string $fieldKey, $value, $context = self::OCCUR_MUST)
    {
        $query = $this->buildTermQuery($fieldKey, $value);
        if ($query != null) {
            $this->queries[$context][] = $query;
        }

        return $this;
    }

    public function addTermInValuesQuery(string $fieldKey, ?string $valueString, $context = self::OCCUR_MUST)
    {
        if ($valueString != null) {
            $queries = $this->buildTermInValuesQuery($fieldKey, $valueString);

            $this->queries[$context][] = [
                'bool' => [
                    'should' => $queries,
                ],
            ];
        }

        return $this;
    }

    public function addQueryToContext(array $query, $context = self::OCCUR_MUST)
    {
        $this->queries[$context][] = $query;

        return $this;
    }

    public function setFilterScript(array $script)
    {
        $this->filterScript = $script;
    }

    public function buildTermInValuesQuery(string $fieldKey, ?string $valueString, $context = self::OCCUR_MUST)
    {
        if ($valueString != null) {
            $valueArr = explode(';', $valueString);
            $queries = [];
            foreach ($valueArr as $value) {
                $queries[] = $this->buildTermQuery($fieldKey, $value);
            }

            return $queries;
        }

        return null;
    }

    public function buildTermQuery(string $fieldKey, $value)
    {
        if ($value !== null) {
            return
                [
                    'match_phrase' => [
                        $fieldKey => (is_bool($value) || is_numeric($value))
                            ? $value
                            : str_replace('+', ' ', $value),
                    ],
                ];
        }

        return null;
    }

    public function buildExistsQuery(string $fieldKey)
    {
        return [
            'exists' => [
                'field' => $fieldKey,
            ],
        ];
    }

    public function setGeoDistance(array $location, ?string $distance): static
    {
        $this->location = $location;
        $this->distance = $distance;

        return $this;
    }

    public function setGlobalAggregate(array $aggregations)
    {
        $this->globalAggregations = $aggregations;

        return $this;
    }

    public function setFilterAggregate(array $aggregations)
    {
        $this->filterAggregations = $aggregations;

        return $this;
    }

    public function paginate(int $page, int $pageSize)
    {
        $this->willPaginate = true;
        $this->page = $page;
        $this->pageSize = $pageSize;
    }

    public function orderRandom(bool $isRandom)
    {
        $this->orderRandom = $isRandom;
    }

    public function orderBy(string $field, string $direction)
    {
        $this->orderField = $field;
        $this->orderDir = $direction;
    }

    public function build(): array
    {
        $result = [];

        if ($this->willPaginate) {
            $result['from'] = max(($this->page - 1) * $this->pageSize, 0);
            $result['size'] = $this->pageSize;
        }

        // Collect valid query context
        $queries = [];
        foreach ($this->queries as $context => $query) {
            if (!empty($query)) {
                $queries[$context] = $query;
            }
        }

        if (!empty($queries)) {
            $query = [
                'bool' => $queries,
            ];

            // building filters
            $filters = [];
            if ($this->filterScript) {
                $filters[] = [
                    'script' => [
                        'script' => $this->filterScript,
                    ],
                ];
            }
            if ($this->location && $this->distance) {
                $filters[] = [
                    'geo_distance' => [
                        'distance' => $this->distance,
                        'location.geo' => $this->location,
                    ],
                ];
            }
            if (!empty($filters)) {
                $query['bool']['filter'] = $filters;
            }

            if ($this->orderRandom) {
                $result['query'] = [
                    'function_score' => [
                        'query' => $query,
                        'functions' => [
                            [
                                'random_score' => new stdClass(),
                            ],
                        ],
                        'score_mode' => 'sum',
                        'boost_mode' => 'replace',
                    ],
                ];
            } else {
                $result['query'] = $query;
            }
        }

        if ($this->orderField === 'distance') {
            $result['sort'] = [[
                '_geo_distance' => [
                    'location.geo' => $this->location,
                    'order' => $this->orderDir,
                ],
            ]];
        } elseif ($this->orderField === 'createdAt') {
            $result['sort'] = [[
                'createdAt' => $this->orderDir,
            ]];
        } elseif ($this->orderField === 'price') {
            $result['sort'] = [[
                '_script' => [
                    'type' => 'number',
                    'script' => [
                        'lang' => 'painless',
                        'source' => 'double price;
                    if(doc[\'websitePrice\'] != null){ price = doc[\'websitePrice\'].value; }
                    if(0 < doc[\'salesPrice\'].value && doc[\'salesPrice\'].value < price) { price = doc[\'salesPrice\'].value; }
                    return price;
                    ',
                    ],
                    'order' => $this->orderDir,
                ],
            ]];
        } elseif ($this->orderField === 'numFeatures') {
            $result['sort'] = [[
                '_script' => [
                    'type' => 'number',
                    'script' => [
                        'lang' => 'painless',
                        'source' => 'int numFeature = 0;
                    if(doc[\'featureList.floorPlan\'] != null){ numFeature += doc[\'featureList.floorPlan\'].size(); }
                    if(doc[\'featureList.stallTack\'] != null){ numFeature += doc[\'featureList.stallTack\'].size(); }
                    if(doc[\'featureList.lq\'] != null){ numFeature += doc[\'featureList.lq\'].size(); }
                    if(doc[\'featureList.doorsWindowsRamps\'] != null){ numFeature += doc[\'featureList.doorsWindowsRamps\'].size(); }
                    return numFeature;
                    ',
                    ],
                    'order' => $this->orderDir,
                ],
            ]];
        }
        if (!empty($result['sort'])) {
            $result['sort'] = array_merge($result['sort'], ['_score']);
        }

        $aggregations = array_merge(
            [],
            $this->globalAggregations ? [
                'all_inventories' => [
                    'global' => new stdClass(),
                    'aggs' => $this->globalAggregations,
                ],
            ] : [],
            $this->filterAggregations ?? []
        );
        if (!empty($aggregations)) {
            $result['aggregations'] = $aggregations;
        }

        return $result;
    }
}
