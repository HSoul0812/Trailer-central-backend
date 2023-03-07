<?php

namespace App\Nova\Filters\Integration;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Filters\Filter;

/**
 * Class EntityReferencesKeyFilter
 * @package App\Nova\Filters\Integration
 */
class EntityReferencesKeyFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = 'Key';

    /**
     * Apply the filter to the given query.
     *
     * @param Request $request
     * @param  Builder  $query
     * @param  mixed  $value
     * @return Builder
     */
    public function apply(Request $request, $query, $value): Builder {
        return $query->where('api_key', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param Request $request
     * @return array
     */
    public function options(Request $request): array {
        return DB::table('api_entity_reference')
            ->select('api_key')
            ->whereNotNull('api_key')
            ->groupBy('api_key')
            ->get()
            ->pluck('api_key', 'api_key')
            ->toArray();
    }
}
