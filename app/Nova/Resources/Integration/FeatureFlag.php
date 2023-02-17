<?php

namespace App\Nova\Resources\Integration;

use App\Models\FeatureFlag as Flag;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;

/**
 * Class Collector
 * @package App\Nova\Resources\Integration
 */
class FeatureFlag extends Resource
{
    public static $group = 'Features';

    /**
     * The model the resource corresponds to.
     *
     *
     * @var string
     */
    public static $model = Flag::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'code';

    public static $search = [
        'process_name',
        'dealer_id'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make('Code')->sortable(),

            Boolean::make('Is Enabled'),

            DateTime::make('Created At')->sortable()->format('DD MMM, YYYY - LT'),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
