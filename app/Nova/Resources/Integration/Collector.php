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
                    Text::make('CDK Username', 'cdk_username')->rules('max:128')->hideFromIndex()->help(
                        "Only needed if file format is CDK"
                    ),
                    Text::make('CDK Password', 'cdk_password')->rules('max:128')->hideFromIndex()->help(
                        "Only needed if file format is CDK"
                    ),
                    Select::make('File Format', 'file_format')
                        ->options(array_combine(CollectorModel::FILE_FORMATS, CollectorModel::FILE_FORMATS))
                        ->displayUsingLabels()
                        ->rules('required'),
                    Text::make('Path To Data', 'path_to_data')->hideFromIndex()->help(
                        'The path to list of items is in the file. For instance, "Units" or "Units/Items" (relevant for xml files)'
                    ),
                ]
            ),

            new Panel('Config', [
                Boolean::make('Import Prices', 'import_prices')->hideFromIndex()->help(
                    'If an option is not selected, price fields (msrp, use_website_price, price, sale_price, website_price, total_of_cost, cost_of_unit) won\'t be imported'
                ),
                Boolean::make('Import Description', 'import_description')->hideFromIndex(),
                Boolean::make('Show On RV Trader', 'show_on_rvtrader')->hideFromIndex(),
                Boolean::make('Use Secondary Image', 'use_secondary_image')->hideFromIndex()->help(
                    'Images in the file are marked as secondary'
                ),
                Boolean::make('Append Floorplan Image', 'append_floorplan_image')->withMeta(['value' => $this->active ?? true])->hideFromIndex(),
                Boolean::make('Update Images', 'update_images')->hideFromIndex(),
                Boolean::make('Update Files', 'update_files')->hideFromIndex(),
                Boolean::make('Import With Showroom Category', 'import_with_showroom_category')->hideFromIndex(),
                Boolean::make('Unarchive Sold Items', 'unarchive_sold_items')->hideFromIndex()->help(
                    'If item exists, but is archived, it will be unarchived upon selecting this option'
                ),
                Text::make('Title Format', 'title_format')->rules('max:128')->hideFromIndex()->help(
                    'Title generation. A list of fields should be separated by commas (by default - "year,manufacturer,model,category")'
                ),
                Text::make('Images Delimiter', 'images_delimiter')->rules('max:128')->hideFromIndex()->help(
                    'Separator between links to images in the file (by default - ",")'
                ),
                Text::make('Overridable Fields', 'overridable_fields')->rules('max:254')->hideFromIndex()->help(
                    'If certain fields shouldn\'t be overwritten after changing these fields in dashboard, it\'s required to specify a list of these fields separated by commas'
                ),
                Text::make('Path To Fields (additional description)', 'path_to_fields_to_description')->rules('max:254')->hideFromIndex()->help(
                    'The path to the fields that should be added in the description. For instance, "Options" or "Config/Options"'
                ),
                Text::make('Fields To Additional Description', 'fields_to_description')->rules('max:254')->hideFromIndex()->help(
                    'The fields that will be added in description. (a list of fields should be separated with a comma)'
                ),
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
