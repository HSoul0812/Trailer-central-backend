<?php

namespace App\Nova\Resources\Dealer;

use App\Nova\Resource;
use Laravel\Nova\Panel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BooleanGroup;
use Illuminate\Database\Eloquent\Model;
use App\Models\Integration\Integration;
use App\Nova\Actions\Dealer\ChangeStatus;
use App\Nova\Actions\Dealer\DeactivateDealer;
use Laravel\Nova\Http\Requests\ActionRequest;
use Trailercentral\PasswordlessLoginUrl\PasswordlessLoginUrl;
use App\Nova\Actions\Dealer\Subscriptions\ManageDealerSubscriptions;
use App\Nova\Actions\Dealer\HiddenIntegrations\ManageHiddenIntegrations;

/**
 * class Dealer
 *
 * @package App\Nova\Resources\Dealer
 */
class Dealer extends Resource
{
    /**
     * The section the resource corresponds to.
     *
     * @var string
     */
    public static $group = 'Dealer';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\User\User';

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
        'dealer_id', 'name', 'email',
    ];

    /**
     *
     * @var array
     */
    public static $with = ['crmUser'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('Dealer ID')->onlyOnForms(),

            PasswordlessLoginUrl::make('Dealer ID', 'dealer_id')->withMeta(['dashboard_url' => config('app.dashboard_login_url')])->exceptOnForms()->sortable(),

            Text::make('Dealership Name', 'name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Primary Email', 'email')
                ->sortable()
                ->rules('required', 'email', 'max:254'),

            Text::make('Stripe ID', 'stripe_id')->exceptOnForms(),

            Text::make('Website', function () {
                if ($this->website) {
                    return $this->website->id;
                } else {
                    return null;
                }
            })->asHtml()->exceptOnForms(),

            Text::make('Websites', function () {
                if ($this->website) {
                    return '<a class="text-primary font-bold" href="http://www.' . $this->website->domain . '" target="_blank">www.' . $this->website->domain . '</a><br>' .
                           '<a class="text-primary font-bold" href="http://' . $this->website->template . '.website-staging.trailercentral.com" target="_blank">' . $this->website->template . '.website-staging.trailercentral.com' . '</a>';
                } else {
                    return null;
                }
            })->asHtml()->exceptOnForms(),

            Text::make('App ID', 'identifier')->exceptOnForms(),

            Text::make('CDK Source ID', 'cdk')->onlyOnDetail(),

            Text::make('Inventories', function () {
                $inventories = \DB::selectOne("select count(inventory_id) as count from inventory where dealer_id = " . $this->dealer_id . " group by dealer_id");
                $inventories = $inventories ? $inventories->count : 0;

                $locations = \DB::selectOne("select count(dealer_location_id) as count from dealer_location where dealer_id = " . $this->dealer_id . " group by dealer_id");
                $locations = $locations ? $locations->count : 0;

                return $inventories . ' units' . '<br>' .
                       $locations . ' locations';
            })->asHtml()->exceptOnForms(),

            new Panel('Subscriptions', $this->subscriptions()),

            new Panel('Integrations', $this->hiddenIntegrations()),

            Text::make('Status', 'state')->exceptOnForms(),

            Boolean::make('Active', function () {
                return !$this->deleted;
            })->exceptOnForms(),

            BelongsTo::make('Collector', 'collector', 'App\Nova\Resources\Integration\Collector')->exceptOnForms(),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:8', 'max:8', 'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/')
                ->updateRules('nullable', 'string', 'min:8', 'max:8', 'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/')
                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    if (!empty($request[$requestAttribute])) {
                        $model->{$attribute} = $request[$requestAttribute];
                    }
                })->help("The password must contain at least three of uppercase letters, lowercase letters, or numbers and at least one of the following !$#%"),

        ];
    }

    /**
     * @return array
     */
    public function subscriptions(): array
    {
        return [
            BooleanGroup::make('Subscriptions')->options([
                'isCdkActive' => 'CDK Leads',
                'isCrmActive' => 'CRM',
                'isDmsActive' => 'DMS',
                'isDealersClassifiedsActive' => 'DealerClassifieds',
                'IsEcommerceActive' => 'E-Commerce',
                'IsGoogleFeedActive' => 'GoogleFeed',
                'isMarketingActive' => 'Marketing',
                'isMobileActive' => 'MobileSite',
                'isPartsActive' => 'Parts',
                'isQuoteManagerActive' => 'QuoteManager',
                'isSchedulerActive' => 'Scheduler',
                'IsUserAccountsActive' => 'UserAccounts',
            ])->withMeta(['value' =>
                [
                    'isCdkActive' => $this->IsCdkActive,
                    'isCrmActive' => $this->isCrmActive,
                    'isDmsActive' => $this->is_dms_active,
                    'isDealersClassifiedsActive' => $this->clsf_active,
                    'IsEcommerceActive' => $this->IsEcommerceActive,
                    'IsGoogleFeedActive' => $this->google_feed_active,
                    'isMarketingActive' => $this->is_marketing_active,
                    'isMobileActive' => $this->isMobileActive,
                    'isPartsActive' => $this->isPartsActive,
                    'isQuoteManagerActive' => $this->is_quote_manager_active,
                    'isSchedulerActive' => $this->is_scheduler_active,
                    'IsUserAccountsActive' => $this->isUserAccountsActive,
                ]
            ])->exceptOnForms()
        ];
    }

    /**
     * @return array
     */
    public function hiddenIntegrations(): array
    {
        return [
            BooleanGroup::make('Hidden Integrations')->options(
                Integration::activeHiddenIntegrations()->pluck('name', 'integration_id')
            )->withMeta(['value' =>
                // We're mapping the active value to bool so Nova can render the tag with the right class
                array_map(function ($v) {
                    return (bool) $v;
                }, $this->integrations->pluck('pivot.active', 'pivot.integration_id')->toArray())
            ])->exceptOnForms()
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
            app()->make(ManageDealerSubscriptions::class),
            app()->make(ManageHiddenIntegrations::class),

            app()->make(DeactivateDealer::class),
            app()->make(ChangeStatus::class),
        ];
    }
}
