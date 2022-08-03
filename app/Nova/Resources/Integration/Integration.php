<?php

namespace App\Nova\Resources\Integration;

use Illuminate\Database\Eloquent\Model;

use App\Nova\Actions\Integration\HideIntegration;
use App\Nova\Actions\Integration\UnhideIntegration;

use App\Nova\Resource;
use Illuminate\Http\Request;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\ActionRequest;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Textarea;

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
            ID::make('Id', 'integration_id')->sortable(),

            Text::make('Code'),

            Text::make('Module Name')->hideFromIndex(),
            Text::make('Module Status')->hideFromIndex(),

            Text::make('Name'),
            Text::make('Description'),
            Text::make('Domain'),

            Text::make('Create Account Url')->hideFromIndex(),

            Boolean::make('Active'),

            Textarea::make('Filters')->hideFromIndex(),
            Textarea::make('Settings')->hideFromIndex(),

            Boolean::make('Include Sold')->hideFromIndex(),

            Textarea::make('Send Email'),

            Boolean::make('Uses Staging'),
            Boolean::make('Show for Integrated'),

            Boolean::make('Is Hidden')->hideWhenUpdating()->hideWhenCreating()
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
