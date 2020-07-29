<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\TextArea;
use Laravel\Nova\Fields\DateTime;

class LeadAssign extends Resource 
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\CRM\Leads\LeadAssign';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'lead_id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('Dealer', 'dealer_id')
                ->sortable(),

            Text::make('Lead', 'lead_id')
                ->sortable(),

            Text::make('Location', 'dealer_location_id'),

            Text::make('Salesperson Type'),

            Text::make('Found', 'found_salesperson_id'),

            Text::make('Assigned', 'chosen_salesperson_id')
                ->sortable(),

            Text::make('Assigned By')
                ->sortable(),

            Text::make('Status')
                ->sortable(),

            TextArea::make('Explanation')
                ->hideFromIndex(),

            DateTime::make('Created At')
                ->format('MM-DD-YYYY hh:mm:ss')
                ->sortable(),
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
            new Filters\DealerIDAssignedLeads
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
