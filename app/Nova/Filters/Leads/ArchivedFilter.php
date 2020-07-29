<?php

namespace App\Nova\Filters\Leads;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;
use Illuminate\Support\Facades\DB;
 
class ArchivedFilter extends Filter
{
    const IS_ARCHIVED_VALUE = 1;
    const IS_NOT_ARCHIVED_VALUE = 0;
    
    const IS_ARCHIVED_LABEL = 'Is Archived';
    const IS_NOT_ARCHIVED_LABEL = 'Is Not Archived';
    
    const IS_ARCHIVED_VALUE_MAPPINGS = [
        self::IS_ARCHIVED_LABEL => self::IS_ARCHIVED_VALUE,
        self::IS_NOT_ARCHIVED_LABEL => self::IS_NOT_ARCHIVED_VALUE
    ];
    
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
        return $query->where('is_archived', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return self::IS_ARCHIVED_VALUE_MAPPINGS;
    }
}
