<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use App\Models\Website\Forms\FieldMap;
use Epartment\NovaDependencyContainer\NovaDependencyContainer;

class JotformFieldMap extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Website\Forms\FieldMap';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'form_field';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'type',
        'form_field',
        'map_field'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        // Initialize Fields Array
        $return = [
            Select::make('Type')
                ->options(FieldMap::MAP_TYPES)
                ->displayUsingLabels()
                ->sortable(),

            Text::make('Form Field')
                ->sortable(),

            Select::make('Map Field')
                ->sortable()
                ->hideWhenCreating()
                ->hideWhenUpdating()
        ];

        // Loop Field Map Field Options
        foreach(FieldMap::MAP_FIELDS as $type => $fields) {
            $return[] = NovaDependencyContainer::make([
                Select::make('Map Field', 'map_field')
                    ->options($fields)
                    ->displayUsingLabels()
                    ->sortable()
                    ->hideFromIndex()
            ])->dependsOn('type', $type);
        }

        // Return FieldMap
        return $return;
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
