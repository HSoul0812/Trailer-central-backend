<?php

namespace App\Nova\Filters\Marketing;

use App\Models\Marketing\Facebook\Error;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class FmiLastRunErrorCode extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = 'Last Error Code';

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
        return $query->where('last_known_error_type', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return array_flip(Error::ERROR_TYPES);
    }
}
