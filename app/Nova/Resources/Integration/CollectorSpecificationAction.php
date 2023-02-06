<?php

namespace App\Nova\Resources\Integration;

use App\Models\Integration\Collector\CollectorFields;
use App\Models\Integration\Collector\CollectorSpecificationAction as CollectorSpecificationActionModel;
use App\Nova\Actions\Exports\CollectorSpecificationActionExport;
use App\Nova\Actions\Importer\CollectorSpecificationActionImporter;
use App\Nova\Resource;
use Epartment\NovaDependencyContainer\NovaDependencyContainer;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

/**
 * Class CollectorSpecificationAction
 * @package App\Nova\Resources\Integration
 */
class CollectorSpecificationAction extends Resource
{
    public static $group = 'Collector';

    /**
     * The model the resource corresponds to.
     *
     *
     * @var string
     */
    public static $model = CollectorSpecificationActionModel::class;

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

            Select::make('Action', 'action')
                ->options(CollectorSpecificationActionModel::ACTION_FORMATS)
                ->displayUsingLabels()
                ->sortable()
                ->rules('required'),

            NovaDependencyContainer::make([
                Select::make('Field', 'field')
                    ->options(CollectorFields::select(['label', 'field'])->orderBy('label')->get()->pluck('label', 'field'))
                    ->rules('required')
                    ->sortable()
                    ->displayUsingLabels(),

                Text::make('Value', 'value')
                    ->rules('required')
                    ->sortable()
                    ->help(
                        'Please note that it\'s required to add Dealer Incoming Mapping record for the set value'
                    ),
            ])->dependsOn('action', CollectorSpecificationActionModel::ACTION_MAPPING)->onlyOnForms(),

            Text::make('Description', function () {
                $description = "<div style='font-size: medium;'>";
                $description .= strtoupper(CollectorSpecificationActionModel::ACTION_FORMATS[$this->action]);

                if ($this->action === CollectorSpecificationActionModel::ACTION_MAPPING) {
                    $description .= ": {$this->field} = {$this->value}";
                }

                $description .= '</div>';

                return $description;
            })->asHtml(),
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
            (new CollectorSpecificationActionExport())->withHeadings()->askForFilename(),
            new CollectorSpecificationActionImporter()
        ];
    }
}
