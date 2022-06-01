<?php

namespace App\Nova\Resources\Dealer;

use App\Nova\Actions\ActivateUserAccounts;
use App\Nova\Actions\DeactivateUserAccounts;
use App\Nova\Actions\Dealer\ActivateCrm;
use App\Nova\Actions\Dealer\DeactivateCrm;
use App\Nova\Actions\Dealer\ActivateECommerce;
use App\Nova\Actions\Dealer\DeactivateECommerce;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\PasswordConfirmation;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use App\Nova\Resource;
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
            Text::make('Dealer ID')->hideFromIndex(),

            PasswordlessLoginUrl::make('Dealer ID', 'dealer_id')->withMeta(['dashboard_url' => config('app.dashboard_login_url')])->onlyOnIndex()->sortable(),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254'),

            Boolean::make('CRM', 'isCrmActive')->hideWhenCreating()->hideWhenUpdating(),

            Boolean::make('ECommerce', 'IsEcommerceActive')->hideWhenCreating()->hideWhenUpdating(),

            Boolean::make('User Accounts', 'isUserAccountsActive')->hideWhenCreating()->hideWhenUpdating(),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:12', 'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/')
                ->updateRules('nullable', 'string', 'min:12', 'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/')
                ->fillUsing(function($request, $model, $attribute, $requestAttribute) {
                    if (!empty($request[$requestAttribute])) {
                        $model->{$attribute} = $request[$requestAttribute];
                    }
                })->help("Password must contain 3 of the following: Uppercase letter, lowercase letter, 0-9 number, non-alphanumeric character, unicode character")
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
            app()->make(ActivateCrm::class),
            app()->make(DeactivateCrm::class),
            app()->make(ActivateECommerce::class),
            app()->make(DeactivateECommerce::class),
            app()->make(ActivateUserAccounts::class),
            app()->make(DeactivateUserAccounts::class),
        ];
    }
}
