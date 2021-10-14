<?php

declare(strict_types=1);

namespace App\Traits\Repository\ElasticSearch;

final class Constants
{
    /**
     * @var float
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-filter-context.html#relevance-scores
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html
     */
    public const EXACT_MATCH_COEFFICIENT = 0.95;
    /** @var float */
    public const MAX_EXPANSIONS = 10;
    /** @var float */
    public const PREFIX_LENGTH = 1;

    /**
     * @var float
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/common-options.html#fuzziness
     */
    public const FUZZY_THRESHOLD = 5;
}
