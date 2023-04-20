<?php

namespace App\Nova\Resources\Integration;

use App\Nova\Resource;
use Illuminate\Http\Request;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

/**
 * class CollectorLog
 * @package App\Nova\Resources\Integration
 *
 * @property $collector
 */
class CollectorLog extends Resource
{
    public static $perPageViaRelationship = 10;
    public static $displayInNavigation = false;

    public static $group = 'Collector';

    /**
     * The model the resource corresponds to.
     *
     *
     * @var string
     */
    public static $model = \App\Models\Integration\Collector\CollectorLog::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'exception',
        'new_items',
        'sold_items',
        'unsold_items',
        'archived_items',
        'unarchived_items',
        'validation_errors'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make('id')
                ->hideFromIndex(),

            BelongsTo::make('Collector', 'collector', Collector::class)
                ->sortable()
                ->rules('required')
                ->readonly(),

            Text::make('New Items', function () {
                return $this->new_items ? count(explode(',', $this->new_items)) : null;
            })->asHtml()->onlyOnIndex(),

            Text::make('New Items')->hideFromIndex(),

            Text::make('Sold Items', function () {
                return $this->sold_items ? count(explode(',', $this->sold_items)) : null;
            })->asHtml()->onlyOnIndex(),

            Text::make('Sold Items')
                ->hideFromIndex(),

            Text::make('Archived Items', function () {
                return $this->archived_items ? count(explode(',', $this->archived_items)) : null;
            })->asHtml()->onlyOnIndex(),

            Text::make('Archived Items')
                ->hideFromIndex(),

            Text::make('Unarchived Items', function () {
                return $this->unarchived_items ? count(explode(',', $this->unarchived_items)) : null;
            })->asHtml()->onlyOnIndex(),

            Text::make('Unarchived Items')
                ->hideFromIndex(),

            Boolean::make('Validation Errors', function () {
                return empty(json_decode($this->validation_errors, true));
            })->onlyOnIndex(),

            Code::make('Validation Errors')->language('javascript')->json()
                ->hideFromIndex(),

            Text::make('Exception'),

            DateTime::make('Created At', 'created_at')
                ->format('DD MMM, YYYY - LT')
                ->exceptOnForms(),
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

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->collector->process_name;
    }
}
