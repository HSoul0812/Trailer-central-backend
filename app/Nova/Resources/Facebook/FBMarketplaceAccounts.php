<?php

namespace App\Nova\Resources\Facebook;

use App\Nova\Actions\ActivateUserAccounts;
use App\Nova\Actions\DeactivateUserAccounts;
use App\Nova\Actions\Dealer\ActivateCrm;
use App\Nova\Actions\Dealer\DeactivateCrm;
use App\Nova\Actions\Dealer\ActivateECommerce;
use App\Nova\Actions\Dealer\DeactivateECommerce;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use App\Nova\Resource;

class FBMarketplaceAccounts extends Resource
{
    public static $group = 'Facebook';

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

    public static function label()
    {
        return 'FB Marketplace Accounts';
    }

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
            Text::make('Dealer ID')->sortable(),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254'),

            Boolean::make('CRM', 'isCrmActive')->hideWhenCreating()->hideWhenUpdating(),
            
            Boolean::make('ECommerce', 'IsEcommerceActive')->hideWhenCreating()->hideWhenUpdating(),

            Boolean::make('User Accounts', 'isUserAccountsActive')->hideWhenCreating()->hideWhenUpdating(),

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

        ];
    }
}
