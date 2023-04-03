<?php

namespace App\Nova\Filters\TransactionExecute;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Nova\Filters\DateFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * class TransactionExecuteDateFromFilter
 *
 * @package App\Nova\Filters\TransactionExecute
 */
class TransactionExecuteDateFromFilter extends DateFilter
{

    public $name = 'Transaction Date From';

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
        return $query->where('queued_at', '>=', Carbon::parse($value));
    }
}
