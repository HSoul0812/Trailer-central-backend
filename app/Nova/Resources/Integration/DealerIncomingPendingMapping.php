<?php

namespace App\Nova\Resources\Integration;

use App\Nova\Actions\Importer\CollectorImporter;
use App\Nova\Actions\Importer\DealerIncomingPendingMappingImporter;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use App\Models\Feed\Mapping\Incoming\DealerIncomingPendingMapping as FeedDealerIncomingPendingMapping;
use App\Nova\Actions\Mapping\MapData;
use App\Nova\Resource;
use App\Nova\Filters\DealerIDPendingMapping;

use App\Nova\Actions\Exports\DealerIncomingPendingMappingExport;

class DealerIncomingPendingMapping extends Resource
{
    public static $group = 'Collector';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Feed\Mapping\Incoming\DealerIncomingPendingMapping';

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
    public static $perPageOptions = [15, 50, 100, 150];

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'dealer_id',
        'data'
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
            Text::make('Dealer ID', 'dealer_id')->sortable(),

            Select::make('Type', 'type')
                ->options(FeedDealerIncomingPendingMapping::$types)
                ->displayUsingLabels(),

            Text::make('Data', 'data'),

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
            new DealerIDPendingMapping()
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
        return [
            (new MapData($this->model()))->exceptOnIndex(),
            (new DealerIncomingPendingMappingExport())->withHeadings()->askForFilename(),
            new DealerIncomingPendingMappingImporter()
        ];
    }

    public function update()
    {
        return false;
    }
}
