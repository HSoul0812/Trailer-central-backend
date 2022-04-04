<?php

namespace App\Services\Inventory;

class ESInventoryQueryBuilder
{
    const OCCUR_MUST = 'must';
    const OCCUR_SHOULD = 'should';
    const OCCUR_MUST_NOT = 'must_not';

    private array $queries = [
        'must' => [],
        'should' => [],
        'must_not' => [],
        'filter' => []
    ];

    private array $fieldSorts = [];
    private bool $willPaginate = false;
    private ?int $page = null;
    private ?int $pageSize = null;
    private ?array $globalAggregations = null;
    private ?array $filterAggregations = null;
    private ?array $location = null;
    private ?string $distance = null;
    private ?array $filterScript = null;

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

    public function rangeQuery(string $fieldKey, $min, $max, $context = self::OCCUR_MUST)
    {
        if ($min != null || $max != null) {
            $rangeQuery = [
                'range' => [
                    $fieldKey => []
                ]
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

    public function termQuery(string $fieldKey, $value, $context = self::OCCUR_MUST)
    {
        $query = $this->_termQuery($fieldKey, $value);
        if ($query != null) {
            $this->queries[$context][] = $query;
        }
        return $this;
    }

    public function termQueries(string $fieldKey, ?string $valueString, $context = self::OCCUR_MUST)
    {
        if ($valueString != null) {
            $valueArr = explode(';', $valueString);
            $queries = [];
            foreach($valueArr as $value) {
                $queries[] = $this->_termQuery($fieldKey, $value);
            }

            $this->queries[$context][] = [
                'bool' => [
                    'should' => $queries
                ]
            ];
        }
        return $this;
    }

    public function setFilterScript(array $script) {
        $this->filterScript = $script;
    }

    private function _termQuery(string $fieldKey, $value) {
        if ($value !== null) {
            return
                [
                    'match_phrase' => [
                        $fieldKey => is_bool($value) ? $value : str_replace("+", " ", $value)
                    ]
                ];
        }
        return null;
    }

    public function geoFiltering(array $location, string $distance)
    {
        $this->location = $location;
        $this->distance = $distance;
        return $this;
    }

    public function globalAggregate(array $aggregations)
    {
        $this->globalAggregations = $aggregations;
        return $this;
    }

    public function filterAggregate(array $aggregations)
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

    public function orderBy(string $field, string $direction) {
        $this->fieldSorts[] = [$field => $direction];
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
        foreach($this->queries as $context => $query) {
            if(!empty($query)) {
                $queries[$context] = $query;
            }
        }

        if (!empty($queries)) {
            $query = [
                'bool' => $queries
            ];

            // building filters
            $filters = [];
            if($this->filterScript) {
                $filters[] = [
                    'script' => [
                        'script' => $this->filterScript
                    ]
                ];
            }
            if($this->location) {
                $filters[] = [
                    'geo_distance' => [
                        'distance' => $this->distance,
                        'location.geo' => $this->location
                    ]
                ];
            }
            if(!empty($filters)) {
                $query['bool']['filter'] = $filters;
            }
            if ($this->location) {
                $result['query'] = [
                    'function_score' => [
                        'query' => $query,
                        'script_score' => [
                            'script' => [
                                'source' => "double d; if(doc['location.geo'].value != null) { d = doc['location.geo'].planeDistance(params.lat, params.lon) * 0.000621371; } else { return 0.1; } if(d >= (params.grouping*params.fromScore)) { return 0.2; } else { return params.fromScore - Math.floor(d/params.grouping);} ",
                                'params' => [
                                    "lat" => $this->location['lat'],
                                    "lon" => $this->location['lon'],
                                    "fromScore" => 100,
                                    "grouping" => 60
                                ]
                            ]
                        ]
                    ]

                ];
            } else {
                $result['query'] = $query;
            }
        }

        if(!empty($this->fieldSorts)) {
            $sorts = array_merge($this->fieldSorts, ["_score"]);
            $result['sort'] = $sorts;
        }

        $aggregations = array_merge(
            [],
            $this->globalAggregations ? [
                "all_inventories" => [
                    "global" => new \stdClass(),
                    "aggs" => $this->globalAggregations
                ]
            ] : [],
            $this->filterAggregations ?? []
        );
        if(!empty($aggregations)) {
            $result['aggregations'] = $aggregations;
        }
        return $result;
    }
}
