<?php

namespace App\Http\Clients\ElasticSearch;

use App\Traits\WithGetter;

/**
 * @property-read HitsResult $hints
 * @property-read AggregationsResult $aggregations
 * @property-read int $total
 */
class ElasticSearchQueryResult
{
    use WithGetter;

    /** @var array */
    private $query;

    /** @var AggregationsResult */
    private $aggregations;

    /** @var HitsResult */
    private $hints;

    /** @var int */
    private $total;

    public function __construct(array $query, array $aggregations, int $total, array $hits)
    {
        $this->query = $query;
        $this->aggregations = AggregationsResult::make($aggregations);
        $this->hints = HitsResult::make($hits);
        $this->total = $total;
    }

    /**
     * @return string
     */
    public function getEncodedESQuery(): string
    {
        return base64_encode(json_encode($this->query));
    }
}
