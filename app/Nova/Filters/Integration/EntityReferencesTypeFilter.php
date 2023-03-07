<?php

namespace App\Nova\Filters\Integration;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Filters\Filter;

/**
 * Class EntityReferencesTypeFilter
 * @package App\Nova\Filters\Integration
 */
class EntityReferencesTypeFilter extends Filter
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
     * @param Request $request
     * @param  Builder  $query
     * @param  mixed  $value
     * @return Builder
     */
    public function apply(Request $request, $query, $value): Builder {
        return $query->where('entity_type', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param Request $request
     * @return array
     */
    public function options(Request $request): array {
        return DB::table('api_entity_reference')
            ->select('entity_type')
            ->whereNotNull('entity_type')
            ->groupBy('entity_type')
            ->get()
            ->pluck('entity_type', 'entity_type')
            ->toArray();
    }
}
