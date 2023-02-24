<?php

namespace App\Nova\Filters\TransactionExecute;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * class TransactionExecuteApiFilter
 *
 * @package App\Nova\Filters\Feeds
 */
class TransactionExecuteApiFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = 'Transaction Api';

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
        return $query->where('api', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param Request $request
     * @return array
     */
    public function options(Request $request): array
    {
        $apis = [];
        $transactions = DB::table('transaction_execute_queue')->select('api')->groupBy('api')->get();

        foreach($transactions as $transaction) {
            $apis[$transaction->api] = $transaction->api;
        }

        return $apis;
    }
}
