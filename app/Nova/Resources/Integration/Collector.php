<?php

namespace App\Nova\Resources\Integration;

use App\Models\Integration\Collector\CollectorFields;
use App\Nova\Actions\Exports\CollectorExport;
use App\Nova\Actions\Importer\CollectorImporter;
use App\Nova\Resource;
use App\Nova\Resources\Dealer\Location;
use App\Nova\Resources\Dealer\LightDealer;
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
use App\Nova\Resources\Integration\CollectorAdminNote as AdminNote;
use App\Models\Integration\Collector\Collector as CollectorModel;

use Epartment\NovaDependencyContainer\HasDependencies;
use Epartment\NovaDependencyContainer\NovaDependencyContainer;

/**
 * Class Collector
 * @package App\Nova\Resources\Integration
 */
class Collector extends Resource
{
    use HasDependencies;

    public static $group = 'Collector';

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
    public static $title = 'process_name';

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

            new Panel('Main', [
                Boolean::make('Is Active', 'active')->withMeta(['value' => $this->active ?? true]),
                Text::make('Process Name')->sortable()->rules('required', 'max:128'),
                BelongsTo::make('Dealer', 'dealers', LightDealer::class)->searchable()->sortable()->rules('required'),
                BelongsTo::make('Default Dealer Location', 'dealerLocation', Location::class)->searchable()->sortable()->rules('required'),
                DateTime::make('Last Run', 'last_run')->sortable()->format('DD MMM, YYYY - LT')->readonly(true)->onlyOnIndex(),
                Boolean::make('Run without Errors', 'run_without_errors')->readonly(true)->onlyOnIndex(),
                DateTime::make('Scheduled For', 'scheduled_for')->sortable()->format('DD MMM, YYYY - LT')->readonly(true)->onlyOnIndex()
            ]),

            new Panel('Source', [
                Select::make('File Format', 'file_format')
                    ->options(array_combine(CollectorModel::FILE_FORMATS, CollectorModel::FILE_FORMATS))
                    ->displayUsingLabels()
                    ->rules('required'),

                // FTP
                NovaDependencyContainer::make([
                    Boolean::make('Use Latest FTP File Only', 'use_latest_ftp_file_only')->hideFromIndex()->help(
                        'Activate if you want the Collector to ignore any FTP file names specified and use the latest file that was dropped'
                    ),

                    Text::make('Host', 'ftp_host')->rules('required', 'max:128')->hideFromIndex(),
                    Text::make('Path To File', 'ftp_path')->rules('required', 'max:128')->hideFromIndex(),
                    Text::make('Login', 'ftp_login')->rules('required', 'max:128')->hideFromIndex(),
                    Text::make('Password', 'ftp_password')
                        ->rules('required', 'max:128', 'alpha_num')
                        ->hideFromIndex()
                        ->help("Password only include A to Z (in any Case) and 0-9 and Recommended of 16 characters"),
                ])->dependsOn('file_format', 'xml')
                    ->dependsOn('file_format', 'csv')
                    ->dependsOn('file_format', 'pipe_delimited')
                    ->dependsOn('file_format', 'csvs'),

                // CDK
                NovaDependencyContainer::make([
                    Text::make('CDK Username', 'cdk_username')->rules('max:128')->hideFromIndex()->help(
                        "Only needed if file format is CDK"
                    ),
                    Text::make('CDK Password', 'cdk_password')->rules('max:128')->hideFromIndex()->help(
                        "Only needed if file format is CDK"
                    ),
                ])->dependsOn('file_format', 'cdk'),

                // CDK MULTIPLE
                NovaDependencyContainer::make([
                    Text::make('CDK Username', 'cdk_username')->rules('max:128')->hideFromIndex()->help(
                        "Only needed if file format is CDK"
                    ),
                    Text::make('CDK Password', 'cdk_password')->rules('max:128')->hideFromIndex()->help(
                        "Only needed if file format is CDK"
                    ),
                    Text::make('CDK DealerCmfs', 'cdk_dealer_cmfs')->rules('required_if:file_format,cdk_multiple')->hideFromIndex()->help(
                        "Only needed if file format is <strong>cdk_multiple</strong>, sparated by comma"
                    ),
                ])->dependsOn('file_format', 'cdk_multiple'),

                // IDS
                NovaDependencyContainer::make([
                    Text::make('IDS Token', 'ids_token')->rules('max:256')->hideFromIndex()->help(
                        "Only needed if file format is IDS"
                    ),
                    Text::make('IDS Default Location', 'ids_default_location')->rules('max:256')->hideFromIndex()->help(
                        "Only needed if file format is IDS"
                    ),
                ])->dependsOn('file_format', 'ids'),

                // XML
                NovaDependencyContainer::make([
                    Text::make('Path To Data', 'path_to_data')->hideFromIndex()->help(
                        'The path to list of items is in the file. For instance, "Units" or "Units/Items" (relevant for xml files)'
                    ),
                ])->dependsOn('file_format', 'xml'),

                // XML URL
                NovaDependencyContainer::make([
                    Text::make('XML URL', 'xml_url')->rules('required_if:file_format,xml_url')->hideFromIndex()->help(
                        "Only needed if file format is <strong>xml_url</strong>"
                    ),

                    Text::make('Path To Data', 'path_to_data')->hideFromIndex()->help(
                        'The path to list of items is in the file. For instance, "Units" or "Units/Items" (relevant for xml files)'
                    ),
                ])->dependsOn('file_format', 'xml_url'),

                // CSV URL
                NovaDependencyContainer::make([
                    Text::make('CSV URL', 'csv_url')->rules('required_if:file_format,csv_url')->hideFromIndex()->help(
                        "Only needed if file format is <strong>csv_url</strong>"
                    ),
                ])->dependsOn('file_format', 'csv_url'),

                // MOTILITY
                NovaDependencyContainer::make([
                    Text::make('Motility Account Number', 'motility_account_no')->hideFromIndex()->help(
                        "Only needed if file format is motility"
                    ),
                    Text::make('Motility Username', 'motility_username')->hideFromIndex()->help(
                        "Only needed if file format is motility"
                    ),
                    Text::make('Motility Password', 'motility_password')->hideFromIndex()->help(
                        "Only needed if file format is motility"
                    ),
                    Text::make('Motility IntegrationID', 'motility_integration_id')->hideFromIndex()->help(
                        "Only needed if file format is motility"
                    ),
                ])->dependsOn('file_format', 'motility'),

                // BISH
                NovaDependencyContainer::make([
                    Text::make('API URL', 'api_url')->hideFromIndex()->help(
                        'Complete endpoint url with http/s to get units. Please if the api url has / include it as well'
                    ),
                    Text::make('Key Name', 'api_key_name')->hideFromIndex()->help(
                        'The name of the key used for authentication to the API to be queried on <strong>header</strong>'
                    )->withMeta(['extraAttributes' => [
                        'placeholder' => 'api-key']
                    ]),
                    Text::make('Key Value', 'api_key_value')->hideFromIndex()->help(
                        'The encrypted/encoded value that authenticates requests to the API. ' .
                        'This value is mandatory if the previous field is filled in. This setting goes on <strong>header</strong>'
                    )->rules('required_if:api_key_name,true'),
                    Code::make('API Params', 'api_params')->hideFromIndex()->help(
                        'It is a key=value params that are used to filter results. ' .
                        'like <strong><code>status=active</code></strong>, please paste it using next format <strong><code>foo=1&bar=2</code></strong> '.
                        'the <strong>&</strong> is needed to separate different parameters'
                    )->withMeta(['extraAttributes' => [
                        'placeholder' => 'status=active']
                    ]),

                    NovaDependencyContainer::make([
                        Number::make('Max Records', 'api_max_records')->hideFromIndex()->help(
                            'Number of Total Records that we can query. ' .
                            'This field has been created for the Bish integration, '.
                            'and a pagination of 3000 records is used in each query. '
                        )->withMeta(['extraAttributes' => [
                            'placeholder' => '8500']
                        ]),
                        Number::make('Pagination', 'api_pagination')->hideFromIndex()->help(
                            'The number of records to be filtered for pagination to be effective. ' .
                            'This field has been created for the Bish integration. ' .
                            'and is required if the previous field is filled.'
                        )->rules('required_if:api_max_records,true')->withMeta(['extraAttributes' => [
                            'placeholder' => '3000']
                        ]),
                    ])->dependsOn('file_format', 'bish'),
                ])->dependsOn('file_format', 'json')
                    ->dependsOn('file_format', 'bish'),
            ]),

            new Panel('BDV', [
                Boolean::make('Activate BDV', 'is_bdv_enabled')->hideFromIndex()->help(
                    'Whether or not to use BDV for this feed (images will be overwritten by whatever bdv sends)'
                )
            ]),

            new Panel('Spincar', [
                Boolean::make('Activate Spincar', 'spincar_active')->hideFromIndex()->help(
                    'Whether or not to use Spincar for this feed (images will be overwritten by whatever spincar sends)'
                ),
                Text::make('Spincar ID', 'spincar_spincar_id')->hideFromIndex()->help(
                    'The dealer ID as provided by Spincar'
                ),
                Text::make('Spincar Filename', 'spincar_filenames')->hideFromIndex()->help(
                    'The Spincar filename being dropped in our FTP'
                ),
            ]),

            new Panel('Factory Settings', [
                Boolean::make('Use Factory Mapping', 'use_factory_mapping')->hideFromIndex()->help(
                    'Whether or not to use the data from FV to populate these units'
                ),
                Boolean::make('Enable MFG and Brand Mapping', 'is_mfg_brand_mapping_enabled')->hideFromIndex()->help(
                    'If enabled will map unit MFG to MFG and unit Brand to Brand.'
                ),
            ]),

            new Panel('Actions With Items', [
                Boolean::make('Create Items')->withMeta(['value' => $this->create_items ?? true])->hideFromIndex(),
                Boolean::make('Update Items')->withMeta(['value' => $this->update_items ?? true])->hideFromIndex(),
                Boolean::make('Archive Items')->withMeta(['value' => $this->archive_items ?? true])->hideFromIndex(),
                Boolean::make('Unarchive Sold Items', 'unarchive_sold_items')->hideFromIndex()->help(
                    'If item exists, but is archived, it will be unarchived upon selecting this option'
                ),
            ]),

            new Panel('Prices', [
                Boolean::make('Import Prices', 'import_prices')->hideFromIndex()->help(
                    'If an option is not selected, price fields (msrp, use_website_price, price, sale_price, website_price, total_of_cost, cost_of_unit) won\'t be imported'
                ),
            ]),

            new Panel('Images, Video and Files', [
                Heading::make('<p class="text-primary"">Files Section</p>')->asHtml(),
                Boolean::make('Update Files', 'update_files')->hideFromIndex(),
                Heading::make('<p class="text-primary"">Images Section</p>')->asHtml(),
                Boolean::make('Update Images', 'update_images')->hideFromIndex(),
                Text::make('Image Directory Address', 'local_image_directory_address')->hideFromIndex()->help(
                    'If the images in the feed are not a URL and instead are uploaded to the FTP include the address to the images here. **Example 1:
                    / -> This would mean the images are in the root directory**
                    **Example 2: /images/ are in the images directory**'
                ),
                Text::make('Images Delimiter', 'images_delimiter')->rules('max:128')->hideFromIndex()->help(
                    'Separator between links to images in the file (by default - ",")'
                ),
                Boolean::make('Use Secondary Image', 'use_secondary_image')->hideFromIndex()->help(
                    'Images in the file are marked as secondary'
                ),
                Boolean::make('Append Floorplan Image', 'append_floorplan_image')->hideFromIndex(),
                Heading::make('<p class="text-primary"">Video Section</p>')->asHtml(),
                Text::make('Video Source Fields', 'video_source_fields')->hideFromIndex()->help(
                    'Please Add with <strong>, separated values without spaces!</strong> all the source file fields that are related to <code>video_embed_code</code><br />
                    <strong>Example:</strong> <code class="font-weight-bold">videourl,virtualtour,alternatevideo,etc</code> '
                ),
            ]),

            new Panel('Title And Description', [
                Text::make('Title Format', 'title_format')->rules('max:128')->hideFromIndex()->help(
                    'Title generation. A list of fields should be separated by commas (by default - "year,manufacturer,model,category")'
                ),
                Boolean::make('Import Description', 'import_description')->hideFromIndex(),
                Text::make('Path To Fields (additional description)', 'path_to_fields_to_description')->rules('max:254')->hideFromIndex()->help(
                    'The path to the fields that should be added in the description. For instance, "Options" or "Config/Options"'
                ),
                Text::make('Fields To Additional Description', 'fields_to_description')->rules('max:254')->hideFromIndex()->help(
                    'The fields that will be added in description. (a list of fields should be separated with a comma)'
                ),
                Text::make('Linebreak Characters', 'linebreak_characters')->hideFromIndex()->help(
                    'Enter the characters that you want to act as linebreak in the description separated by commas. For example if you want this feed to use * and | as line breaks you would enter: *,|'
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

            new Panel('Other Options', [
                Boolean::make('Zero Out MSRP On Used Units', 'zero_msrp')->hideFromIndex(),
                Boolean::make('Show On RV Trader', 'show_on_rvtrader')->hideFromIndex(),
                Boolean::make('Import With Showroom Category', 'import_with_showroom_category')->hideFromIndex(),
                Text::make('Overridable Fields', 'overridableFieldsList')->onlyOnDetail(),
                BooleanGroup::make('Overridable Fields', 'overridable_fields')->options(
                    CollectorFields::select(['label', 'field'])->orderBy('label')->get()->pluck('label', 'field')
                )->onlyOnForms(),
                Text::make('Skip Units By Category', 'skip_categories')->hideFromIndex()->help(
                    'Enter the categories (as they show in the source file) you would like to skip separated by commas. Example: trailer, vehicle, car'
                ),
                Text::make('Skip Units By Location', 'skip_locations')->hideFromIndex()->help(
                    'Enter the locations (as they show in the source file) you would like to skip separated by commas. Example: Grand Rapids, New York City, Miami'
                ),
                Text::make('Types Affected By the Feed', 'only_types')->hideFromIndex()->help(
                    'Enter the types of the inventory you want this feed to affect separated by commas. For example if you want this feed to affect only trailers and boats you would enter: 1,5'
                ),
                Boolean::make('Ignore Manually Added Units', 'ignore_manually_added_units')->hideFromIndex()->help(
                    'If active any manually added units will be ignored by the feed'
                ),
                Boolean::make('Show On Auction 123', 'show_on_auction123')->hideFromIndex(),
            ]),

            HasMany::make('Specifications', 'specifications', CollectorSpecification::class),

            HasMany::make('Admin Notes', 'collectorAdminNotes', AdminNote::class),

            HasMany::make('Change Reports', 'collectorChangeReports', CollectorChangeReport::class)
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
    public function actions(Request $request): array
    {
        return [
            (new CollectorExport())->withHeadings()->askForFilename(),
            new CollectorImporter()
        ];
    }
}
