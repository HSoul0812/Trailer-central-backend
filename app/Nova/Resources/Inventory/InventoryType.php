<?php

namespace App\Nova\Resources\Inventory;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use App\Nova\Resource;

class InventoryType extends Resource
{    
    public static $group = 'Inventory';
    
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Inventory\EntityType';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'title'
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
            Text::make('ID', 'entity_type_id')
                ->sortable()
                ->rules('required'),
            
            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Title')
                ->sortable()
                ->rules('required', 'max:255'),
            
            Text::make('Title Lowercase')
                ->sortable()
                ->rules('required', 'max:255'),
            
            Text::make('Sort Order')
                ->sortable()
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
