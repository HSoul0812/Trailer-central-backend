<?php

namespace App\Nova;

use App\Models\Feed\Feed as FeedModel;
use App\Nova\Actions\StartDealerIncomingFeed;
use App\Nova\Actions\StartDealerOutgoingFeed;
use App\Nova\Actions\StartFactoryFeed;
use App\Nova\Filters\FeedType;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class Feed extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Feed\Feed';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The pagination per-page options configured for this resource.
     *
     * @return array
     */
    public static $perPageOptions = [50, 100, 150];

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('Feed Name', 'name')->sortable(),

            Select::make('Feed Type', 'type')
                ->options(\App\Models\Feed\Feed::$types)
                ->displayUsingLabels(),

            Select::make('Status', 'status')
                ->options(\App\Models\Feed\Feed::$statuses)
                ->showOnDetail(), // status

            Select::make('Frequency', 'frequency')->options([
                '86400' => 'Daily',
                '3600' => 'Every Hour',
                '10800' => 'Every 3 Hours',
                '21600' => 'Every 6 Hours',
                '43200' => 'Every 12 Hours',
            ])->hideFromIndex(), // status

            Textarea::make('Description', 'description')
                ->hideFromIndex(),

            // show data source options only when generation is 'current'
            Select::make('Data Source', 'data_source')
                ->options(\App\Models\Feed\Feed::$dataSources)
                ->showOnDetail(), // status

            Select::make('Data Format', 'data_format')
                ->options([
                    '' => 'n/a',
                    'csv' => 'CSV',
                    'tsv' => 'TSV',
                    'xml' => 'XML',
                ])
                ->showOnDetail(), // status

            KeyValue::make('Data Source Parameters', 'data_source_params'),

            Text::make('Run Status', 'module_status')
                ->hideWhenCreating()
                ->hideWhenUpdating(), // status

            Text::make('Send Email', 'send_email')
                ->hideFromIndex(),

            Text::make('Code', 'code')
                ->hideFromIndex(),

            Text::make('Module', 'module_name')
                ->hideFromIndex(),


            Text::make('Domain', 'domain')
                ->hideFromIndex(),

            Text::make('Create Account URL', 'create_account_url')
                ->hideFromIndex(),

            Boolean::make('Include Sold', 'include_sold')
                ->hideFromIndex(),

            Text::make('Last Run Date', 'last_run_at')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->readonly()
                ->sortable(), // last run

            KeyValue::make('Filters', 'filters')
                ->hideFromIndex(),

            KeyValue::make('Settings', 'settings')
                ->hideFromIndex(),

        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new FeedType()
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        $startDealerIncomingFeed = new StartDealerIncomingFeed();
        $startDealerIncomingFeed->showOnIndex = false;
        $startDealerOutgoingFeed = new StartDealerOutgoingFeed();
        $startDealerOutgoingFeed->showOnIndex = false;
        $startFactoryFeed = new StartFactoryFeed();
        $startFactoryFeed->showOnIndex = false;

        // actions on this resource
        return [
            $startDealerIncomingFeed
                ->showOnDetail()
                ->canSee(function ($request) {
                    return optional($request->findModelQuery()->first())->type === FeedModel::TYPE_DEALER_INCOMING_FEED;
                }),

            $startDealerOutgoingFeed
                ->showOnDetail()
                ->canSee(function ($request) {
                    return optional($request->findModelQuery()->first())->type === FeedModel::TYPE_DEALER_OUTGOING_FEED;
                }),

            $startFactoryFeed
                ->showOnDetail()
                ->canSee(function ($request) {
                    return optional($request->findModelQuery()->first())->type === FeedModel::TYPE_FACTORY_FEED;
                }),

        ];
    }
}
