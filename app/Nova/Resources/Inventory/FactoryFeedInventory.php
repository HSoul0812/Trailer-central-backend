<?php

namespace App\Nova\Resources\Inventory;

use App\Nova\Resource;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;

class FactoryFeedInventory extends Resource
{
    public static $group = 'Factory Feed Inventories';

    public static $displayInNavigation = false;

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
        'stock', 'vin', 'code'
    ];

    /**
     * @param Request $request
     * @return false
     */
    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make('Inventory Id', 'inventory_id'),
            Text::make('Code', 'code'),
            Text::make('Stock', 'stock'),
            Text::make('Vin', 'vin'),
            DateTime::make('Created At')->sortable()->format('DD MMM, YYYY - LT'),
            DateTime::make('Updated At')->sortable()->format('DD MMM, YYYY - LT')
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
        return [
            //
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
        return [
           //
        ];
    }

    /**
     * @param Request $request
     * @return false
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    /**
     * @param Request $request
     * @return false
     */
    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }


    /**
     * @param Request $request
     * @return false
     */
    public function authorizedToView(Request $request): bool
    {
        return false;
    }
}
