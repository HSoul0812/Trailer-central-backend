<?php

namespace App\Nova\Resources\Facebook;

use App\Nova\Actions\Dealer\ClearFBMEErrors;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use App\Nova\Resource;
use Laravel\Nova\Panel;

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
        'dealer_id', 'dealer_name', 'fb_username', 'units_posted_today'
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
            new Panel('FB Integration Details', $this->panelIntegration()),

            new Panel("FBME Run Status", $this->panelStatus()),

            new Panel("Results " . date("m-d-Y", strtotime("-1 day")), $this->panelResults(1)),
            new Panel("Results " . date("m-d-Y", strtotime("-2 day")), $this->panelResults(2)),
            new Panel("Results " . date("m-d-Y", strtotime("-3 day")), $this->panelResults(3)),
            new Panel("Results " . date("m-d-Y", strtotime("-4 day")), $this->panelResults(4)),
            new Panel("Results " . date("m-d-Y", strtotime("-5 day")), $this->panelResults(5)),

        ];
    }

    protected function panelIntegration()
    {
        return [
            Text::make('Integration ID', 'id')->onlyOnDetail(),
            
            Text::make('Dealer ID', 'dealer_id')->sortable(),

            Text::make('Dealer Name', 'dealer_name')
                ->sortable(),

            Text::make('FB Username')
                ->sortable(),

            Text::make('Location')
                ->sortable(),


        ];
    }

    protected function panelStatus()
    {
        return [
            DateTime::make('Last Run', 'last_run_ts')
                ->sortable(),

            Boolean::make('Status', 'last_run_status')
                ->sortable(),

            Text::make('Units Posted Today', 'units_posted_today'),

            Text::make('Error Today', 'error_today'),
        ];
    }

    protected function panelResults(int $nrDaysAgo)
    {
        return [
            Text::make('Units Posted', "units_posted_{$nrDaysAgo}dayago")->onlyOnDetail(),
            Text::make('Last Error', "error_{$nrDaysAgo}dayago")->onlyOnDetail(),
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
            (app()->make(ClearFBMEErrors::class))->canSee(function ($request) {
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
