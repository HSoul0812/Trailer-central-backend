<?php

namespace App\Nova\Resources\Leads;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use App\Nova\Filters\Leads\DealerIDLeads;
use App\Nova\Filters\Leads\DateSubmittedAfterFilter;
use App\Nova\Filters\Leads\DateSubmittedBeforeFilter;
use App\Nova\Actions\Leads\ArchiveLeads;
use App\Nova\Actions\Leads\DeleteLeads;
use App\Nova\Filters\Leads\ArchivedFilter;
use App\Nova\Resource;

class WebsiteLead extends Resource
{
    public static $group = 'Leads';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\CRM\Leads\Lead';

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
        'title',
        'first_name',
        'last_name',
        'dealer_id',
        'email_address',
        'phone_number',
        'address',
        'note'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('Title')
                ->sortable(),

            Text::make('First Name')
                ->sortable(),

            Text::make('Last Name')
                ->sortable(),

            Text::make('Dealer ID', 'dealer_id')->sortable(),

            Text::make('Email Address')->sortable(),

            Text::make('Phone Number')->sortable(),

            Text::make('Address')->sortable(),

            Text::make('Archived', 'is_archived')->sortable(),

            Text::make('Date Submitted')->sortable(),

            Text::make('Note')
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new DealerIDLeads,
            new DateSubmittedAfterFilter,
            new DateSubmittedBeforeFilter,
            new ArchivedFilter
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new ArchiveLeads)
                ->canSee(function ($request) {
                    return true;
                })->canRun(function ($request, $user) {
                    return true;
                }),
            (new DeleteLeads)
                ->canSee(function ($request) {
                    return false;
                })->canRun(function ($request, $user) {
                    return false;
                })
        ];
    }
}
