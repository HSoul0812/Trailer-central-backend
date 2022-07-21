<?php

namespace App\Nova\Resources\Facebook;

use App\Nova\Actions\Dealer\ClearFBMEErrors;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use App\Nova\Resource;

class FBMarketplaceAccounts extends Resource
{
    public static $group = 'Facebook';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\CRM\Dealer\DealerFBMOverview';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'fb_username', 'units_posted'
    ];

    public static function label()
    {
        return 'FB Marketplace Accounts';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('Dealer ID', 'id')->sortable(),

            Text::make('Dealer Name', 'name')
                ->sortable(),

            Text::make('FB Username')
                ->sortable(),

            Text::make('Location')
                ->sortable(),

            DateTime::make('Last Run', 'last_run_ts')
                ->sortable(),

            Boolean::make('Status', 'last_run_status')
                ->sortable(),

            Text::make('Units Posted', 'units_posted'),

            Text::make('Last Error')
                ->sortable()

        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
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
    public function actions(Request $request)
    {
        return [
            (new ClearFBMEErrors)->canSee(function ($request) {
                return true;
            })->canRun(function ($request) {
                return true;
            })->onlyOnTableRow(),
        ];
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }
}
