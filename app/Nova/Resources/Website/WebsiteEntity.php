<?php

namespace App\Nova\Resources\Website;

use App\Nova\Filters\WebsiteEntity\WebsiteId;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class WebsiteEntity extends Resource
{
    public static $group = 'Websites';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Website\Entity';

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
        'website_id',
        'title',
        'cms_content'
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
            Text::make('Website ID')->exceptOnForms(),

            BelongsTo::make('Website'),

            Text::make('Entity Type')->hideFromIndex(),

            Text::make('Entity View'),

            Text::make('Template')->hideFromIndex(),

            Text::make('Parent')->hideFromIndex(),

            Text::make('Title'),

            Text::make('Url Path')->hideFromIndex(),

            Text::make('Url Path External')->hideFromIndex(),

            Text::make('Sort Order')->hideFromIndex(),

            Boolean::make('In Nav')->hideFromIndex(),

            Boolean::make('Deleted'),

            Boolean::make('Active', 'is_active')->sortable(),
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
            new WebsiteId,
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
        return [

        ];
    }
}
