<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * class FactoryCodeFilter
 *
 * @package App\Nova\Filters\Feeds
 */
class FactoryCodeFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = 'Factory Code';

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
        return $query->where('code', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param Request $request
     * @return array
     */
    public function options(Request $request): array
    {
        $codes = [];
        $uploads = DB::table('feed_api_uploads')->select('code')->groupBy('code')->get();

        foreach($uploads as $upload) {
            $codes[$upload->code] = $upload->code;
        }

        return $codes;
    }
}
