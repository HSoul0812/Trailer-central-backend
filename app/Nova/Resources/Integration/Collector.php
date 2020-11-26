<?php

namespace App\Nova\Resources\Integration;

use App\Nova\Resource;
use App\Nova\Resources\Dealer\Dealer;
use App\Nova\Resources\Dealer\Location;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;
use App\Models\Integration\Collector\Collector as CollectorModel;

/**
 * Class Collector
 * @package App\Nova\Resources\Integration
 */
class Collector extends Resource
{
    public static $group = 'Integration';

    /**
     * The model the resource corresponds to.
     *
     *
     * @var string
     */
    public static $model = CollectorModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static $search = [
        'process_name',
        'dealer_id'
    ];

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

            new Panel('Main',
                [
                    Boolean::make('Is Active', 'active')->withMeta(['value' => $this->active ?? true]),
                    Text::make('Process Name')->sortable()->rules('required', 'max:128'),
                    BelongsTo::make('Dealer', 'dealers', Dealer::class)->sortable()->rules('required'),
                    BelongsTo::make('Default Dealer Location', 'dealerLocation', Location::class)->sortable()->rules('required'),
                ]
            ),

            new Panel('Source',
                [
                    Text::make('Host', 'ftp_host')->rules('required', 'max:128')->hideFromIndex(),
                    Text::make('Path To File', 'ftp_path')->rules('required', 'max:128')->hideFromIndex(),
                    Text::make('Login', 'ftp_login')->rules('required', 'max:128')->hideFromIndex(),
                    Text::make('Password', 'ftp_password')->rules('required', 'max:128')->hideFromIndex(),
                    Select::make('File Format', 'file_format')
                        ->options(array_combine(CollectorModel::FILE_FORMATS, CollectorModel::FILE_FORMATS))
                        ->displayUsingLabels()
                        ->rules('required'),
                ]
            ),

            new Panel('Config', [
                Boolean::make('Import Prices', 'import_prices'),
                Boolean::make('Import Description', 'import_description'),
                Boolean::make('Show On RV Trader', 'show_on_rvtrader'),
                Text::make('Title Format', 'title_format')->rules('max:128')->hideFromIndex(),
                Text::make('Images Delimiter', 'images_delimiter')->rules('max:128')->hideFromIndex(),
            ]),

            new Panel('Measures', [
                Select::make('Length Format', 'length_format')
                    ->options(array_flip(CollectorModel::MEASURE_FORMATS))
                    ->displayUsingLabels()
                    ->hideFromIndex(),
                Select::make('Width Format', 'width_format')
                    ->options(array_flip(CollectorModel::MEASURE_FORMATS))
                    ->displayUsingLabels()
                    ->hideFromIndex(),
                Select::make('Height Format', 'height_format')
                    ->options(array_flip(CollectorModel::MEASURE_FORMATS))
                    ->displayUsingLabels()
                    ->hideFromIndex(),
            ]),
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
