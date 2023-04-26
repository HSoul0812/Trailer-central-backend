<?php

namespace App\Nova\Resources\Craigslist;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use App\Nova\Resource;
use App\Nova\Resources\Dealer\LightDealer;

class ClappBalance extends Resource
{

    public static $group = 'Marketplaces';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Marketing\Craigslist\Balance';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'dealer_id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'dealer_id'
    ];

    public static function label(): string
    {
        return 'Craiglist Balances';
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
            /*
             * 2 specific fields, 1 to create Clapp Balance
             * the other one is just informative, and avoid block edit values when the user already have been associated here
             * */
            BelongsTo::make('Dealer', 'user', LightDealer::class)->searchable()->sortable()->rules('required')->hideWhenUpdating()->showOnCreating()->hideFromDetail(),

            Text::make('Dealer Information', 'user', function ($model) {
                return $model->dealer_id . ' - ' . $model->name;
            })->asHtml()->showOnUpdating()->hideFromIndex()->hideWhenCreating()->readonly(),

            Currency::make('Balance')
                ->textAlign('right')
                ->sortable(),

            DateTime::make('Last Updated')
                ->sortable()
                ->hideWhenCreating()
                ->hideWhenUpdating(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
