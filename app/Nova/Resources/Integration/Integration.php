<?php

namespace App\Nova\Resources\Integration;

use Illuminate\Database\Eloquent\Model;

use App\Models\Integration\Integration as IntegrationModel;
use App\Nova\Actions\Integration\HideIntegration;
use App\Nova\Actions\Integration\UnhideIntegration;

use App\Nova\Resource;
use Illuminate\Http\Request;

use Laravel\Nova\Http\Requests\ActionRequest;

use Laravel\Nova\Panel;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Textarea;

use Laravel\Nova\Fields\Code;

class Integration extends Resource
{
    public static $group = 'Integration';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Integration\Integration';

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
        'integration_id',
        'code',
        'name'
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
            Text::make('Id', 'integration_id', function() {
                return IntegrationModel::latest('integration_id')->first()->integration_id + 1;
            })->showOnCreating()->hideFromDetail()->hideWhenUpdating()->hideFromIndex()->sortable(),

            Text::make('Id', 'integration_id')->hideWhenCreating()->sortable(),

            Text::make('Code'),

            Text::make('Module Name')->hideFromIndex(),
            Text::make('Module Status')->default('idle')->hideFromIndex(),

            Text::make('Name'),
            Text::make('Description'),
            Text::make('Domain')->help(
                "Please, only include the domain here, e.g: trailercentral.com"
            ),
            Text::make('Create Account Url')->hideFromIndex(),

            Boolean::make('Active'),

            Code::make('Filters', 'unserializeFilters')->language('javascript')->json(),
            Code::make('Settings', 'unserializeSettings')->language('javascript')->json()->help(
                "Please, when using package options, to set unlimited/all units set key as '0', e.g:
                'options': {
                    '0': 'Unlimited'
                }"
            ),

            Boolean::make('Include Sold')->hideFromIndex(),

            Textarea::make('Send Email'),

            Boolean::make('Uses Staging'),
            Boolean::make('Show for Integrated'),

            Boolean::make('Is Hidden')->hideWhenUpdating()->hideWhenCreating(),

            new Panel('Main', [
                HasMany::make('Integration Dealers', 'dealers', DealerIntegration::class),
            ]),
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
        return [
            app()->make(HideIntegration::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && !$this->resource->isHidden;
            }),
            app()->make(UnhideIntegration::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && $this->resource->isHidden;
            }),
        ];
    }
}
