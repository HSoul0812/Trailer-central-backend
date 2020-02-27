<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class Feed extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Feed';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'integration_id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
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
            Text::make('Send Email', 'send_email')->hideFromIndex(),

            Text::make('Feed Name', 'name')->sortable(),

            Select::make('Feed Type', 'type')->options([
                'dealer_outgoing_feed' => 'Dealer Outgoing Feed',
                'dealer_incoming_feed' => 'Dealer Incoming Feed',
                'factory_feed' => 'Factory Feed',
            ])->displayUsingLabels(),

            DateTime::make('Last Run Date', 'last_run_at')
                ->format('YYYY-MM-DD HH:mm')
                ->sortable(), // last run

            Select::make('Status', 'module_status')->options([
                'idle' => 'Idle',
                'about-to-run' => 'About to run',
                'running' => 'Running',
                'need-assistance' => 'Needs Assistance',
            ]), // status

            Text::make('Code', 'code')->hideFromIndex(),

            Text::make('Module', 'module_name')->hideFromIndex(),

            Select::make('Generation', 'module_name')->options([
                'legacy' => 'Legacy',
                'current' => 'Current',
            ])->hideFromIndex(),

            Textarea::make('Description', 'description')->hideFromIndex(),

            Text::make('Domain', 'domain')->hideFromIndex(),

            Text::make('Create Account URL', 'create_account_url')->hideFromIndex(),

            Boolean::make('Is Active', 'active')->hideFromIndex(),

            Boolean::make('Include Sold', 'include_sold')->hideFromIndex(),

            Textarea::make('Filters', 'filters')->hideFromIndex(),

            Textarea::make('Settings', 'settings')->hideFromIndex(),

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
