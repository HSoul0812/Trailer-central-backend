<?php

namespace App\Nova\Resources\Dealer;

use App\Nova\Actions\ActivateUserAccounts;
use App\Nova\Actions\DeactivateUserAccounts;
use App\Nova\Actions\Dealer\DeactivateDealer;
use App\Nova\Actions\Dealer\DeactivateECommerce;
use App\Nova\Actions\Dealer\HiddenIntegrations\ActivateELeads;
use App\Nova\Actions\Dealer\HiddenIntegrations\DeactivateELeads;
use App\Nova\Actions\Dealer\Subscriptions\CDK\ActivateCdk;
use App\Nova\Actions\Dealer\Subscriptions\CRM\ActivateCrm;
use App\Nova\Actions\Dealer\Subscriptions\CRM\DeactivateCrm;
use App\Nova\Actions\Dealer\Subscriptions\DealerClassifieds\ActivateDealerClassifieds;
use App\Nova\Actions\Dealer\Subscriptions\DealerClassifieds\DeactivateDealerClassifieds;
use App\Nova\Actions\Dealer\Subscriptions\DMS\DeactivateDms;
use App\Nova\Actions\Dealer\Subscriptions\ECommerce\ActivateECommerce;
use App\Nova\Actions\Dealer\Subscriptions\Marketing\ActivateMarketing;
use App\Nova\Actions\Dealer\Subscriptions\Marketing\DeactivateMarketing;
use App\Nova\Actions\Dealer\Subscriptions\Parts\ActivateParts;
use App\Nova\Actions\Dealer\Subscriptions\Parts\DeactivateParts;
use App\Nova\Actions\Dealer\Subscriptions\QuoteManager\ActivateQuoteManager;
use App\Nova\Actions\Dealer\Subscriptions\Scheduler\ActivateScheduler;
use App\Nova\Actions\Dealer\Subscriptions\Scheduler\DeactivateScheduler;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\PasswordConfirmation;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Panel;
use Trailercentral\PasswordlessLoginUrl\PasswordlessLoginUrl;

class Dealer extends Resource
{
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

    public static $with = ['crmUser'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
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
                return count($this->inventories) . ' units' . '<br>' .
                       count($this->locations) . ' locations';
            })->asHtml()->exceptOnForms(),

            new Panel('Subscriptions', $this->subscriptions()),

            new Panel('Integrations', $this->hiddenIntegrations()),

            Text::make('State', 'state')->exceptOnForms(),

            Boolean::make('Deleted', 'deleted')->exceptOnForms(),

            BelongsTo::make('Collector', 'collector', 'App\Nova\Resources\Integration\Collector')->exceptOnForms(),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:12', 'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/')
                ->updateRules('nullable', 'string', 'min:12', 'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/')
                ->fillUsing(function($request, $model, $attribute, $requestAttribute) {
                    if (!empty($request[$requestAttribute])) {
                        $model->{$attribute} = $request[$requestAttribute];
                    }
                })->help("Password must contain 3 of the following: Uppercase letter, lowercase letter, 0-9 number, non-alphanumeric character, unicode character"),

        ];
    }

    public function subscriptions(): array
    {
        return [
            BooleanGroup::make('Subscriptions')->options([
                'isCdkActive' => 'CDK Leads',
                'isCrmActive' => 'CRM',
                'isDmsActive' => 'DMS',
                'isDealersClassifiedsActive' => 'DealerClassifieds',
                'IsEcommerceActive' => 'E-Commerce',
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

    public function hiddenIntegrations(): array
    {
        return [
            BooleanGroup::make('Hidden Integrations')->options([
                'IsAuction123Active' => 'Auction123',
                'IsAutoConxActive' => 'AutoConx',
                'IsCarbaseActive' => 'Carbase',
                'IsDP360Active' => 'DP360',
                'IsELeadsActive' => 'E-Leads',
                'IsTrailerUsaActive' => 'TrailerUSA',
            ])->withMeta(['value' =>
                [
                    'IsAuction123Active' => $this->IsAuction123Active,
                    'IsAutoConxActive' => $this->IsAutoConxActive,
                    'IsCarbaseActive' => $this->IsCarbaseActive,
                    'IsDP360Active' => $this->IsDP360Active,
                    'IsELeadsActive' => $this->IsELeadsActive,
                    'IsTrailerUsaActive' => $this->IsTrailerUsaActive,
                ]
            ])->exceptOnForms()
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
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function actions(Request $request): array
    {
        return [
            app()->make(ActivateCdk::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && !$this->resource->isCdkActive;
            }),
            app()->make(\App\Nova\Actions\Dealer\Subscriptions\CDK\DeactivateCdk::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && $this->resource->isCdkActive;
            }),
            app()->make(ActivateCrm::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && !$this->resource->isCrmActive;
            }),
            app()->make(DeactivateCrm::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && $this->resource->isCrmActive;
            }),
            app()->make(ActivateDealerClassifieds::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && !$this->resource->clsf_active;
            }),
            app()->make(DeactivateDealerClassifieds::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && $this->resource->clsf_active;
            }),
            app()->make(\App\Nova\Actions\Dealer\Subscriptions\DMS\ActivateDms::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && !$this->resource->is_dms_active;
            }),
            app()->make(DeactivateDms::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && $this->resource->is_dms_active;
            }),
            app()->make(ActivateECommerce::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && !$this->resource->isEcommerceActive;
            }),
            app()->make(DeactivateECommerce::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && $this->resource->isEcommerceActive;
            }),
            app()->make(ActivateELeads::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && !$this->resource->isELeadsActive;
            }),
            app()->make(DeactivateELeads::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && $this->resource->isELeadsActive;
            }),
            app()->make(ActivateMarketing::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && !$this->resource->isMarketingActive;
            }),
            app()->make(DeactivateMarketing::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && $this->resource->isMarketingActive;
            }),
            app()->make(\App\Nova\Actions\Dealer\Subscriptions\MobileSite\ActivateMobileSite::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && !$this->resource->isMobileActive;
            }),
            app()->make(\App\Nova\Actions\Dealer\Subscriptions\MobileSite\DeactivateMobileSite::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && $this->resource->isMobileActive;
            }),
            app()->make(ActivateParts::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && !$this->resource->isPartsActive;
            }),
            app()->make(DeactivateParts::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && $this->resource->isPartsActive;
            }),
            app()->make(ActivateQuoteManager::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && !$this->resource->is_quote_manager_active;
            }),
            app()->make(\App\Nova\Actions\Dealer\Subscriptions\QuoteManager\DeactivateQuoteManager::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && $this->resource->is_quote_manager_active;
            }),
            app()->make(ActivateScheduler::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && !$this->resource->is_scheduler_active;
            }),
            app()->make(DeactivateScheduler::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && $this->resource->is_scheduler_active;
            }),
            app()->make(ActivateUserAccounts::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && !$this->resource->isUserAccountsActive;
            }),
            app()->make(DeactivateUserAccounts::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && $this->resource->isUserAccountsActive;
            }),
            app()->make(DeactivateDealer::class)->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof Model && !$this->resource->deleted;
            }),
        ];
    }

    public function validateActionState($request, bool $validation): bool
    {
        if ($request instanceof ActionRequest) {
            return true;
        }

        return $this->resource instanceof Model && $validation;
    }
}
