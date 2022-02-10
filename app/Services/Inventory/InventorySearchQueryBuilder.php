<?php

namespace App\Services\Inventory;

class InventorySearchQueryBuilder
{
    private array $queries = [];
    private array $fieldSorts = [];
    private bool $willPaginate = false;
    private ?int $page = null;
    private ?int $pageSize = null;
    private ?array $globalAggregations = null;
    private ?array $filterAggregations = null;
    private ?array $geoScore = null;
    private ?string $filterScript = null;

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

    public function rangeQuery(string $fieldKey, $min, $max)
    {
        if ($min != null || $max != null) {
            $rangeQuery = [
                'range' => [
                    $fieldKey => []
                ]
            ];
            if ($min != null) {
                $rangeQuery['range'][$fieldKey]['gte'] = $min;
            }
            if ($max != null) {
                $rangeQuery['range'][$fieldKey]['lte'] = $max;
            }

            $this->queries[] = $rangeQuery;
        }
        return $this;
    }

    public function termQuery(string $fieldKey, ?string $value)
    {
        $query = $this->_termQuery($fieldKey, $value);
        if ($query != null) {
            $this->queries[] = $query;
        }
        return $this;
    }

    public function termQueries(string $fieldKey, ?string $valueString)
    {
        if ($valueString != null) {
            $valueArr = explode(';', $valueString);
            $queries = [];
            foreach($valueArr as $value) {
                $queries[] = $this->_termQuery($fieldKey, $value);
            }

            $this->queries[] = [
                'bool' => [
                    'should' => $queries
                ]
            ];
        }
        return $this;
    }

    public function setFilterScript(string $script) {
        $this->filterScript = $script;
    }

    private function _termQuery(string $fieldKey, ?string $value) {
        if ($value !== null) {
            return
                [
                    'match_phrase' => [
                        $fieldKey => str_replace("+", " ", $value)
                    ]
                ];
        }
        return null;
    }

    public function geoScoring(float $lat, float $lng)
    {
        $this->geoScore = [
            'lat' => $lat,
            'lon' => $lng
        ];
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

        if (count($this->queries) > 0) {
            $query = [
                'bool' => [
                    'must' => $this->queries
                ]
            ];
            if($this->filterScript) {
                $query['bool']['filter'] = [
                    'script' => [
                        'script' => $this->filterScript
                    ]
                ];
            }

            if ($this->geoScore) {
                $result['query'] = [
                    'function_score' => [
                        'query' => $query,
                        'script_score' => [
                            'source' => "double d; if(doc['location.geo'].value != null) { d = doc['location.geo'].planeDistance(params.lat, params.lng) * 0.000621371; } else { return 0.1; } if(d >= (params.grouping*params.fromScore)) { return 0.2; } else { return params.fromScore - Math.floor(d\/params.grouping); ",
                            'params' => [
                                "lat" => $this->geoScore['lat'],
                                "lng" => $this->geoScore['lon'],
                                "fromScore" => 100,
                                "grouping" => 60
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

        $result['aggregations'] = array_merge(
            [],
            $this->globalAggregations ? [
                "all_inventories" => [
                    "global" => new \stdClass(),
                    "aggs" => $this->globalAggregations
                ]
            ] : [],
            $this->filterAggregations ?? []
        );
        return $result;
    }
}
