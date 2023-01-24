<?php

namespace App\Nova\Resources\Integration;

use App\Nova\Resource;

use App\Nova\Resources\Dealer\Dealer;
use App\Nova\Resources\Dealer\LightDealer;
use App\Nova\Resources\Dealer\Location;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\BelongsTo;

use App\Models\Feed\Mapping\ExternalDealerMapping as EDM;

class ExternalDealerMapping extends Resource
{
    public static $group = 'Integration';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = EDM::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'external_dealer_mapping_id';

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
        'external_provider',
        'external_id',
        'dealer_id',
        'dealer_location_id'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make('Id', 'external_dealer_mapping_id'),

            Text::make('External Provider')->sortable(),

            Text::make('External Id'),

            BelongsTo::make('Dealer', 'dealer', LightDealer::class)->searchable()->sortable()->rules('required'),

            BelongsTo::make('Dealer Location', 'dealerLocation', Location::class)->searchable()->sortable()->rules('required'),

            Boolean::make('Active', 'is_active'),

            Boolean::make('Valid', 'is_valid'),

            DateTime::make('Created At')->sortable(),
            DateTime::make('Updated At')->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request): array
    {
        return [];
    }
}
