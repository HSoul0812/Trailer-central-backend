<?php

namespace App\Nova\Resources\Integration;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BooleanGroup;

use App\Models\Integration\Collector\CollectorFields;

/**
 * Class Collector
 * @package App\Nova\Resources\Integration
 */
class CollectorField extends Resource
{
    public static $group = 'Collector';

    /**
     * The model the resource corresponds to.
     *
     *
     * @var string
     */
    public static $model = CollectorFields::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'label';

    public static $search = [
        'label',
        'field',
        'type'
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $defaultSort = 'label';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Label')->sortable(),
            Text::make('Field')->sortable(),
            Text::make('Type')->sortable(),

            Boolean::make('Is Mapped', 'mapped')->sortable(),
            Boolean::make('Is Boolean', 'boolean')->sortable()
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
