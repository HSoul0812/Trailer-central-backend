<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;
use Illuminate\Support\Facades\DB;
 
class DealerIDPendingMapping extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->where('dealer_id', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $dealerOut = [];
        $dealers = DB::table('dealer_incoming_pending_mapping')->select('dealer_id')->groupBy('dealer_id')->get();
        
        foreach($dealers as $dealer) {
            $dealerOut[$dealer->dealer_id] = $dealer->dealer_id;
        }        
        
        return $dealerOut;
    }
}