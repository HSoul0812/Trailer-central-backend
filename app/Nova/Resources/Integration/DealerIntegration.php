<?php

namespace App\Nova\Resources\Integration;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsTo;
use App\Nova\Resources\Dealer\LightDealer;

/**
 * class DealerIntegration
 *
 * @package App\Nova\Resources\Integration
 */
class DealerIntegration extends Resource
{
    public static $displayInNavigation = false;

    /**
     * The section the resource corresponds to.
     *
     * @var string
     */
    public static $group = 'Integration';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\User\Integration\DealerIntegration';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'integration_dealer_id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'integration_id','dealer_id'
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
            BelongsTo::make('Integration', 'integration', Integration::class),
            BelongsTo::make('Dealer', 'dealer', LightDealer::class),

            Boolean::make('Active'),

            Boolean::make('Include Pending Sale'),

            DateTime::make('Last Run At')->exceptOnForms(),

            DateTime::make('Created At')->exceptOnForms(),
            DateTime::make('Updated At')->exceptOnForms(),

            Code::make('Settings', function () {
                return $this->settings ? json_encode(unserialize($this->settings)) : null;
            })->language('javascript')->json()->onlyOnDetail(),
            Code::make('Filters', function () {
                return $this->filters ? json_encode(unserialize($this->filters)) : null;
            })->onlyOnDetail(),

            Text::make('Location Ids')
                ->hideFromIndex()
                ->help('Location Ids will go separated by comma. E.g 123,321,231 and so on.'),
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
        return [];
    }
}
