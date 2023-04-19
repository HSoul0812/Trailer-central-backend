<?php

namespace App\Nova\Filters\Marketing;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class FmiLastRunStatus extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = 'Last Run';

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
        if($value === 'complete') {
            return $query->where('last_attempt_posts_remaining','=',0);
        }if($value === 'partial') {
            return $query->where('last_attempt_posts', '>', 0)
                ->whereRaw('last_attempt_posts < posts_per_day');
        }if($value === 'fail') {
            return $query->where('last_attempt_posts', '=', 0);
        }
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
            'Complete' => 'complete',
            'Partial' => 'partial',
            'Fail' => 'fail',
        ];
    }
}
