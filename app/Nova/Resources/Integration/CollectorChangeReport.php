<?php

namespace App\Nova\Resources\Integration;

use App\Nova\Resource;
use App\Nova\User;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Text;

class CollectorChangeReport extends Resource
{
    public static $perPageViaRelationship = 10;
    public static $displayInNavigation = false;

    public static $group = 'Integration';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Integration\Collector\CollectorChangeReport';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'field';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'field'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            BelongsTo::make('Collector', 'collector', Collector::class)
                ->sortable()
                ->rules('required')
                ->readonly(),

            BelongsTo::make('User', 'user', User::class)
                ->sortable()
                ->rules('required')
                ->readonly(),

            Text::make('Field Name', 'field')
                ->rules('required')
                ->readonly(),

            Text::make('Changed From', 'changed_from')
                ->sortable()
                ->rules('required')
                ->readonly()
                ->displayUsing(function($value) {
                    return strlen($value) > 40 ? substr($value, 0, 40) . '...' : $value;
                }),

            Text::make('Changed To', 'changed_to')
                ->sortable()
                ->rules('required')
                ->readonly()
                ->displayUsing(function($value) {
                    return strlen($value) > 40 ? substr($value, 0, 40) . '...' : $value;
                }),

            DateTime::make('Changed At', 'created_at')
                ->readonly(),
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
