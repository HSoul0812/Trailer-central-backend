<?php

namespace App\Nova\Filters;

use App\Models\Feed;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class FeedType extends Filter
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
        return [
            'Dealer Outgoing Feed' => Feed::TYPE_DEALER_OUTGOING_FEED,
            'Dealer Incoming Feed' => Feed::TYPE_DEALER_INCOMING_FEED,
            'Factory Feed' => Feed::TYPE_FACTORY_FEED,
        ];
    }
}
