<?php

namespace App\Nova\Filters\Integration;

use App\Models\Feed\Mapping\Incoming\DealerIncomingMapping as FeedDealerIncomingMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Filters\Filter;

/**
 * Class IncomingMappingsTypeFilter
 * @package App\Nova\Filters\Integration
 */
class IncomingMappingsTypeFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = 'Type';

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
        return $query->where('type', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return DB::table('dealer_incoming_mappings')
            ->select('type')
            ->whereNotIn('type', [FeedDealerIncomingMapping::DEFAULT_VALUES, FeedDealerIncomingMapping::FIELDS])
            ->groupBy('type')
            ->get()
            ->pluck('type', 'type')
            ->toArray();
    }
}
