<?php

namespace App\Nova\Resources\Integration;

use App\Nova\Resource;
use Illuminate\Http\Request;

// Metrics
use App\Nova\Metrics\FactoryFeed\LamarMetric;
use App\Nova\Metrics\FactoryFeed\LgsMetric;
use App\Nova\Metrics\FactoryFeed\LoadTrailMetric;
use App\Nova\Metrics\FactoryFeed\NorstarMetric;
use App\Nova\Metrics\FactoryFeed\NovaeMetric;
use App\Nova\Metrics\FeedApiUploadMetric;
use App\Nova\Metrics\FeedApiUploadTrendMetric;

// Filters
use App\Nova\Filters\FactoryCodeFilter;
use App\Nova\Filters\Feeds\FactoryDateToFilter;
use App\Nova\Filters\Feeds\FactoryDateFromFilter;

// Fields
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;

use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Feed\Uploads\FeedApiUpload as FeedUpload;

class FeedApiUpload extends Resource
{
    public static $group = 'Factory Feeds';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = FeedUpload::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'key';

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
        'code',
        'key'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Code'),

            Text::make('Key'),

            Text::make('Type'),

            Code::make('Data')->language('javascript')->json(),

            DateTime::make('Created At')
                ->sortable(),

            DateTime::make('Updated At')
                ->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     * @return array
     */
    public function cards(Request $request): array
    {
        return [
            new NorstarMetric(),
            new LoadTrailMetric(),
            new LgsMetric(),
            new LamarMetric(),
            new NovaeMetric(),
            new FeedApiUploadMetric(),
            new FeedApiUploadTrendMetric()
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function filters(Request $request): array
    {
        return [
            new FactoryCodeFilter(),
            new FactoryDateFromFilter(),
            new FactoryDateToFilter()
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function lenses(Request $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function actions(Request $request): array
    {
        return [];
    }
}
