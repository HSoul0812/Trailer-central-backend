<?php

namespace App\Indexers;

use App\Traits\WithGetter;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @property-read LengthAwarePaginator $hints
 * @property-read array $aggregations
 */
class ElasticSearchQueryResult
{
    use WithGetter;

    /** @var array */
    private $aggregations;

    /** @var LengthAwarePaginator */
    private $hints;

    public function __construct(array $aggregations, LengthAwarePaginator $hints)
    {
        $this->aggregations = $aggregations;
        $this->hints = $hints;
    }
}
