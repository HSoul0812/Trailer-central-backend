<?php

namespace App\Nova\Filters\Feeds;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Nova\Filters\DateFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * class FactoryDateFrom
 *
 * @package App\Nova\Filters\Feeds
 */
class FactoryDateFromFilter extends DateFilter
{

    /**
     * Apply the filter to the given query.
     *
     * @param Request $request
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function apply(Request $request, $query, $value): Builder
    {
        return $query->where('created_at', '>=', Carbon::parse($value));
    }
}
