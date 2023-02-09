<?php

namespace App\Nova\Resources\Integration;

use App\Models\Integration\Collector\CollectorFields;
use App\Nova\Actions\Importer\FieldMappingImporter;
use App\Nova\Filters\DealerIDMapping;
use App\Nova\Resource;
use App\Nova\Resources\Dealer\LightDealer;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Feed\Mapping\Incoming\DealerIncomingMapping;

use App\Nova\Actions\Exports\FieldMappingExport;

class FieldMapping extends Resource
{
    public static $group = 'Collector';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Feed\Mapping\Incoming\DealerIncomingMapping';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The pagination per-page options configured for this resource.
     *
     * @return array
     */
    public static $perPageOptions = [15, 50, 100, 150];

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'map_from',
        'map_to',
        'dealer_id'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('Incoming Field', 'map_from')->rules('required')->sortable()->help(
                'For example, "Category". If it\'s needed, the path can be specified (for instance, Details/Category)'
            ),

            Select::make('Our Field', 'map_to')
                ->options(CollectorFields::select(['label', 'field'])->orderBy('label')->get()->pluck('label', 'field'))
                ->rules('required')
                ->sortable()
                ->displayUsingLabels()
                ->help(
                    '<span style="color: red">Important! The following fields must be specified: manufacturer, category, status. If some of the fields is absent in the file, the default value should be specified. (Default Value Mappings)</span>'
                ),

            BelongsTo::make('Dealer', 'dealers', LightDealer::class)->searchable()->sortable()->rules('required'),

            Text::make('', 'type')->withMeta([
                'type' => 'hidden',
                'value'=> $this->type ?? 'fields'
            ])->onlyOnForms()
        ];
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('type', DealerIncomingMapping::FIELDS);
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new DealerIDMapping
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new FieldMappingExport())->withHeadings()->askForFilename(),
            new FieldMappingImporter()
        ];
    }
}
