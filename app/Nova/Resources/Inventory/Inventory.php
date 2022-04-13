<?php

namespace App\Nova\Resources\Inventory;

use App\Nova\Actions\Inventory\UnblockToCollector;
use App\Nova\Filters\DealerIDMapping;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Panel;


class Inventory extends Resource
{
    public static $group = 'Inventory';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Inventory\Inventory';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';


    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'dealer_id', 'stock'
    ];

    /**
     * @param Request $request
     * @return false
     */
    public static function authorizedToCreate(Request $request) {
        return false;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request) {
        return [
            Number::make('Dealer Id', 'dealer_id')
                ->sortable()
                ->withMeta(['extraAttributes' => [
                    'readonly' => true,
                    'disabled' => true,
                ]]),
            Text::make('Stock', 'stock')
                ->sortable()
                ->withMeta(['extraAttributes' => [
                    'readonly' => true,
                    'disabled' => true,
                ]]),
            Text::make('Vin', 'vin')
                ->sortable()
                ->withMeta(['extraAttributes' => [
                    'readonly' => true,
                    'disabled' => true,
                ]]),
            Text::make('Model', 'model')
                ->sortable()
                ->withMeta(['extraAttributes' => [
                    'readonly' => true,
                    'disabled' => true,
                ]]),
            Text::make('Manufacturer', 'manufacturer')
                ->sortable()
                ->withMeta(['extraAttributes' => [
                    'readonly' => true,
                    'disabled' => true,
                ]]),
            Number::make('Year', 'year')
                ->sortable()
                ->withMeta(['extraAttributes' => [
                    'readonly' => true,
                    'disabled' => true,
                ]]),
            Boolean::make('Changed Fields In Dashboard')
                ->trueValue(1)
                ->falseValue(0)
                ->onlyOnIndex()
                ->withMeta([
                    'value' => empty($this->changed_fields_in_dashboard) ? 0 : 1,
                ]),
            Text::make('Changed Fields In Dashboard', 'changed_fields_in_dashboard')
                ->hideFromIndex()
                ->withMeta(['extraAttributes' => [
                    'readonly' => true,
                    'disabled' => true,
                ]])->help(
                    'If you see a list in this field, there are fields that have been changed in the dashboard manually '
                ),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     * @return array
     */
    public function cards(Request $request) {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function filters(Request $request) {
        return [
            new DealerIDMapping
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function lenses(Request $request) {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function actions(Request $request) {
        return [
            app()->make(UnblockToCollector::class),
        ];
    }

    /**
     * @param Request $request
     * @return false
     */
    public function authorizedToDelete(Request $request) {
        return false;
    }

    /**
     * @param Request $request
     * @return false
     */
    public function authorizedToUpdate(Request $request) {
        return true;
    }


    /**
     * @param Request $request
     * @return false
     */
    public function authorizedToView(Request $request) {
        return true;
    }
}
