<?php

namespace App\Nova;

use App\Models\Feed\Feed as FeedModel;

use App\Nova\Actions\StartDealerIncomingFeed;
use App\Nova\Actions\StartDealerOutgoingFeed;
use App\Nova\Actions\StartFactoryFeed;

use Illuminate\Http\Request;

use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\DateTime;

use App\Nova\Filters\Feeds\FeedType;
use App\Nova\Filters\Feeds\FeedStatus;


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
                ->sortable()
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
                ->hideFromIndex()
                ->showOnCreating()
                ->showOnDetail(), // status

            KeyValue::make('Data Source Parameters', 'data_source_params'),

            Text::make('Run Status', 'module_status')
                ->showOnDetail()
                ->hideWhenCreating()
                ->hideWhenUpdating(), // status

            KeyValue::make('Notify Emails', 'notify_email')
                ->keyLabel('Email')
                ->valueLabel('Name')
                ->hideFromIndex(),

            Text::make('Module Code', 'code')
                ->hideFromIndex(),

            Text::make('Module Class', 'module_name')
                ->hideFromIndex(),

            KeyValue::make('Other Settings', 'settings')
                ->hideFromIndex(),

            DateTime::make('Last Run Start', 'last_run_start')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->readonly()
                ->sortable(), // last run

            DateTime::make('Last Run End', 'last_run_end')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->readonly()
                ->sortable(), // last run

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
            new FeedType(),
            new FeedStatus(),
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
