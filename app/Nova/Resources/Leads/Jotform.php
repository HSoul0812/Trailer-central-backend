<?php

namespace App\Nova\Resources\Leads;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use App\Nova\Resource;
use App\Nova\Filters\Leads\JotformEnabledWebsites;

class Jotform extends Resource 
{
    public static $group = 'Leads';
    
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\CRM\Leads\Jotform\WebsiteForms';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'url';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'url',
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
            Text::make('URL', 'url')
                ->sortable(),

            Text::make('Website ID', 'website_id')
                ->sortable(),
            
            Text::make('Jotform ID', 'jotform_id'),

            Text::make('Title', 'title'),

            Text::make('Status', 'status'),
            
            Text::make('Username', 'username')
        ];
    }

    /**
     * Get the fields for index
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fieldsForIndex(Request $request)
    {
        return [
            Text::make('URL', 'url')
                ->sortable(),

            Text::make('Website ID', 'website_id')
                ->sortable(),
            
            Text::make('Jotform ID', 'jotform_id'),

            Text::make('Title', 'title'),

            Text::make('Status', 'status')
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
        return [
            new JotformEnabledWebsites
        ];
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
