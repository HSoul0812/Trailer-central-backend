<?php

namespace App\Nova\Resources\Facebook;

use App\Nova\Actions\Dealer\ClearFBMEErrors;
use App\Nova\Actions\FME\DownloadIntegrationRunHistory;
use App\Nova\Actions\FME\DownloadRunHistory;
use App\Nova\Metrics\Marketing\FmeDealersAttempted;
use App\Nova\Metrics\Marketing\FmeErrors;
use App\Nova\Metrics\Marketing\FmeIntegrations;
use App\Nova\Metrics\Marketing\FmeListings;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use App\Nova\Resource;
use Laravel\Nova\Panel;

class FBMarketplaceAccounts extends Resource
{
    public static $group = 'Facebook';
    public static $orderBy = ['last_attempt_ts' => 'asc'];

    public static $tableStyle = 'tight';

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

    public static function label(): string
    {
        return 'FB Marketplace Accounts';
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
            new Panel('FB Integration Details', $this->panelIntegration()),

            new Panel("FBME Status", $this->panelStatus()),

            new Panel("Today's status", $this->panelTodaysResults()),

            new Panel("Results " . date("m-d-Y", strtotime("-1 day")), $this->panelResults(1)),
            new Panel("Results " . date("m-d-Y", strtotime("-2 day")), $this->panelResults(2)),
            new Panel("Results " . date("m-d-Y", strtotime("-3 day")), $this->panelResults(3)),
            new Panel("Results " . date("m-d-Y", strtotime("-4 day")), $this->panelResults(4)),
            new Panel("Results " . date("m-d-Y", strtotime("-5 day")), $this->panelResults(5)),

        ];
    }

    protected function panelIntegration(): array
    {
        return [
            Text::make('ID', 'id'),

            Text::make('Dealer ID', 'dealer_id')->sortable(),

            Text::make('Dealer Name', 'dealer_name')
                ->sortable(),

            Text::make('FB Username')
                ->sortable(),

            Text::make('Location')
                ->sortable(),
        ];
    }

    protected function panelStatus(): array
    {
        return [
            DateTime::make('Last Attempt', 'last_attempt_ts')
            ->sortable(),

            Boolean::make('Last Status', 'last_run_status')
                ->sortable(),

            DateTime::make('Last Error', 'last_known_error_ts')
                ->sortable(),

            Text::make('Last Error Code', 'last_known_error_code')
                ->sortable(),

            Text::make('Last Error Message', 'last_known_error_message')
                ->onlyOnDetail(),

            DateTime::make('Last Success', 'last_success_ts')
                ->sortable(),

            Text::make('Lastest Posts', 'last_units_posted'),

        ];
    }

    protected function panelTodaysResults(): array
    {
        return [
            Text::make('Units Posted', "units_posted_today")->onlyOnDetail(),
            Text::make('Last Error', "error_today")->onlyOnDetail(),
        ];
    }

    protected function panelResults(int $nrDaysAgo): array
    {
        return [
            Text::make('Units Posted', "units_posted_{$nrDaysAgo}dayago")->onlyOnDetail(),
            Text::make('Last Error', "error_{$nrDaysAgo}dayago")->onlyOnDetail(),
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
        return [
            new FmeErrors,
            new FmeListings,
            new FmeDealersAttempted,
            new FmeIntegrations,
        ];
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
     * @throws BindingResolutionException
     */
    public function actions(Request $request): array
    {
        return [
            $this->clearErrorsAction(),
            $this->downloadIntegrationRunHistoryAction(),
            $this->downloadRunHistoryAction(),
        ];
    }

    /**
     * @throws BindingResolutionException
     */
    private function clearErrorsAction(): ClearFBMEErrors
    {
        return (app()->make(ClearFBMEErrors::class))->canSee(function () {
            return true;
        })->canRun(function () {
            return true;
        })->onlyOnTableRow();
    }

    /**
     * @throws BindingResolutionException
     */
    private function downloadIntegrationRunHistoryAction(): DownloadIntegrationRunHistory
    {
        return (app()->make(DownloadIntegrationRunHistory::class))->canSee(function () {
            return true;
        })->canRun(function () {
            return true;
        })->onlyOnTableRow();
    }

    /**
     * @throws BindingResolutionException
     */
    private function downloadRunHistoryAction(): DownloadRunHistory
    {
        return (app()->make(DownloadRunHistory::class))->canSee(function () {
            return true;
        })->canRun(function () {
            return true;
        })->onlyOnIndex();
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }
}
