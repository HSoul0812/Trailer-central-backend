<?php

namespace App\Nova\Resources\Integration;

use App\Models\Feed\Mapping\Incoming\ApiEntityReference as AER;
use App\Nova\Filters\Integration\EntityReferencesKeyFilter;
use App\Nova\Filters\Integration\EntityReferencesTypeFilter;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Text;

class ApiEntityReference extends Resource
{
    public static $group = 'Integration';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = AER::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'api_entity_reference_id';

    /**
     * The pagination per-page options configured for this resource.
     *
     * @return array
     */
    public static $perPageOptions = [15, 50, 100, 150];

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'entity_id',
        'reference_id',
        'entity_type',
        'api_key'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request): array {
        return [
            Text::make('Entity Type')->sortable(),

            Text::make('Entity ID')->sortable(),

            Text::make('Reference ID')->sortable(),

            Text::make('API Key')->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request): array {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request): array {
        return [
            new EntityReferencesTypeFilter,
            new EntityReferencesKeyFilter
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request): array {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request): array {
        return [];
    }

    /**
     * @param $query
     * @param $search
     * @return Builder
     */
    protected static function applySearch($query, $search): Builder
    {
        if (Str::lower($search) === 'dealer') {
        return parent::applySearch($query, $search)
            ->orWhere(DB::raw('entity_type like \'dealer%\''));
        }

        return parent::applySearch($query, $search);
    }

}
