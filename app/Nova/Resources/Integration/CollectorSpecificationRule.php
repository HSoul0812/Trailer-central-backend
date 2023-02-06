<?php

namespace App\Nova\Resources\Integration;

use App\Models\Integration\Collector\CollectorSpecificationRule as CollectorSpecificationRuleModel;
use App\Nova\Actions\Exports\CollectorSpecificationRuleExport;
use App\Nova\Actions\Importer\CollectorSpecificationRuleImporter;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

/**
 * Class CollectorSpecificationRule
 * @package App\Nova\Resources\Integration
 */
class CollectorSpecificationRule extends Resource
{
    public static $group = 'Collector';

    /**
     * The model the resource corresponds to.
     *
     *
     * @var string
     */
    public static $model = CollectorSpecificationRuleModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static $search = [

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
            BelongsTo::make('Specification For', 'collectorSpecification', CollectorSpecification::class)
                ->showOnIndex()
                ->showOnDetail()
                ->sortable()
                ->rules('required'),

            Select::make('Condition', 'condition')
                ->options(CollectorSpecificationRuleModel::CONDITION_FORMATS)
                ->displayUsingLabels()
                ->sortable()
                ->rules('required')
                ->help(
                    'Please note that conditions differ in data type.
                    I.e. if a string is checked, the condition can be: Same, Not Same, Contains, Not Contains.
                    If a number is checked, the condition can be: Equal, Not Equal, Less Than, Less Than Or Equal, Greater Than, Greater Than Or Equal'
                ),

            Text::make('Incoming Field', 'field')
                ->sortable()
                ->rules('required')
                ->help(
                    'For example, "Category". If it\'s needed, the path can be specified (for instance, Details/Category)<br>
                    Service fields:<br>
                    ###service_data_url### - relevant when several files from different sources are specified for one collector'
                ),

            Text::make('Incoming Value', 'value')
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
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function filters(Request $request): array
    {
        return [];
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
        return [
            (new CollectorSpecificationRuleExport())->withHeadings()->askForFilename(),
            new CollectorSpecificationRuleImporter()
        ];
    }
}
