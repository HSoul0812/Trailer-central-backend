<?php

namespace App\Nova\Resources\Dealer;

use App\Models\User\DealerLocation;
use App\Nova\Resources\Location\Geolocation;
use App\Services\User\DealerLocationServiceInterface;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use App\Nova\Resource;

class Location extends Resource
{
    public static $group = 'Dealer';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\User\DealerLocation';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'dealer_id', 'name', 'email'
    ];

    /**
     * @var DealerLocationServiceInterface
     */
    private $dealerLocationService;

    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->dealerLocationService = app(DealerLocationServiceInterface::class);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make('Dealer Location ID')->sortable(),

            Text::make('Dealer ID')->sortable(),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('App ID', 'identifier')->exceptOnForms(),

            Text::make('Phone'),

            Text::make('Street', 'address')->rules('required'),

            Text::make('City')->hideWhenCreating()->readonly(),

            Text::make('Zip Code', 'postalcode')->hideWhenCreating()->readonly(),

            Text::make('Country')->hideWhenCreating()->readonly(),

            BelongsTo::make('Zip Code', 'possibleGeolocationByZip', Geolocation::class)
                ->hideCreateRelationButton()
                ->hideFromIndex()
                ->hideWhenUpdating()
                ->hideFromDetail()
                ->searchable()
                ->fillUsing(function (NovaRequest $request, DealerLocation $model) {
                    $geolocationId = $request->get('possibleGeolocationByZip');
                    $this->dealerLocationService->fillGeolocationDetails($geolocationId, $model);
                }),

            BelongsTo::make('Enter new zip code for change in location', 'possibleGeolocationByZip', Geolocation::class)
                ->hideCreateRelationButton()
                ->hideFromIndex()
                ->hideWhenCreating()
                ->hideFromDetail()
                ->searchable()
                ->fillUsing(function (NovaRequest $request, DealerLocation $model) {
                    $geolocationId = $request->get('possibleGeolocationByZip');
                    $this->dealerLocationService->updateFilledGeolocationDetails($geolocationId, $model);
                })
                ->nullable(),

            new Panel('Google', [
                Text::make('Google Store Code', 'google_business_store_code')
            ])
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
