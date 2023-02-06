<?php

namespace App\Nova\Resources\Integration;

use App\Nova\Actions\Exports\CollectorSpecificationExport;
use App\Nova\Actions\Importer\CollectorSpecificationImporter;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Select;
use \App\Models\Integration\Collector\CollectorSpecification as CollectorSpecificationModel;
use \App\Models\Integration\Collector\CollectorSpecificationAction as CollectorSpecificationActionModel;
use Laravel\Nova\Fields\Text;

/**
 * Class CollectorSpecification
 * @package App\Nova\Resources\Integration
 *
 * @property \App\Models\Integration\Collector\CollectorSpecificationRule[] $rules
 * @property CollectorSpecificationActionModel[] $actions
 */
class CollectorSpecification extends Resource
{
    public static $group = 'Collector';

    /**
     * The model the resource corresponds to.
     *
     *
     * @var string
     */
    public static $model = CollectorSpecificationModel::class;

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return $this->collector->process_name;
    }

    public static $search = [
        'collector'
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
            BelongsTo::make('Collector', 'collector', Collector::class)
                ->sortable()
                ->rules('required'),

            Select::make('Logical Operator', 'logical_operator')
                ->options([
                    CollectorSpecificationModel::LOGICAL_OPERATOR_AND => 'Satisfied if all conditions are met (AND)',
                    CollectorSpecificationModel::LOGICAL_OPERATOR_OR => 'Satisfied if at least one condition is met (OR)',
                ])
                ->displayUsingLabels()
                ->rules('required')
                ->hideFromIndex(),

            Text::make('Description', function () {
                $description = '<div style="display: grid; grid-template-columns: 0.3fr 0.8fr 0.3fr 0.5fr"><div><strong>IF</strong></div>';

                $description .= '<div>';

                foreach ($this->rules as $key => $rule) {
                    $description .= "<div style='font-size: medium;'>";
                    $description .= $key !== 0 ? ('<strong>' . strtoupper($this->logical_operator) . '</strong> ') : '';
                    $description .= "{$rule->field} <strong>" .  strtoupper($rule->condition) . "</strong> {$rule->value}";
                    $description .= '</div>';
                }

                $description .= '</div>';

                $description .= '<div><strong>THAN</strong></div>';

                foreach ($this->actions as $key => $action) {
                    $description .= "<div style='font-size: medium;'>";
                    $description .= $key !== 0 ? ('<strong> AND </strong> ') : '';
                    $description .= strtoupper(CollectorSpecificationActionModel::ACTION_FORMATS[$action->action]);

                    if ($action->action === CollectorSpecificationActionModel::ACTION_MAPPING) {
                        $description .= ": {$action->field} = {$action->value}";
                    }

                    $description .= '</div>';
                }

                $description .= '</div>';

                return $description;
            })->asHtml(),

            HasMany::make('Rules', 'rules', CollectorSpecificationRule::class),
            HasMany::make('Actions', 'actions', CollectorSpecificationAction::class)
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
            (new CollectorSpecificationExport())->withHeadings()->askForFilename(),
            new CollectorSpecificationImporter()
        ];
    }
}
