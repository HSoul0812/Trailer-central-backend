<?php

namespace App\Nova\Resources\Mapping;

use Epartment\NovaDependencyContainer\HasDependencies;
use Epartment\NovaDependencyContainer\NovaDependencyContainer;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use App\Models\Feed\Mapping\Incoming\DealerIncomingMapping as FeedDealerIncomingMapping;
use App\Nova\Resource;
use App\Nova\Filters\DealerIDMapping;

class DealerIncomingMapping extends Resource
{
    use HasDependencies;

    const MAP_TO_MANUFACTURER = 'map_to_manufacturer';
    const MAP_TO_BRAND = 'map_to_brand';

    public static $group = 'Mapping';
    
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Feed\Mapping\Incoming\DealerIncomingMapping';

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
        'map_from',
        'map_to',
        'dealer_id'
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
            Text::make('Map From', 'map_from')->sortable(),

            NovaDependencyContainer::make([
                Text::make('Map To', 'map_to')->sortable()
            ])->dependsOnNot('type', FeedDealerIncomingMapping::MANUFACTURER_BRAND),

            NovaDependencyContainer::make([
                Text::make('Map To Manufacturer', self::MAP_TO_MANUFACTURER)->sortable()
            ])->dependsOn('type', FeedDealerIncomingMapping::MANUFACTURER_BRAND),

            NovaDependencyContainer::make([
                Text::make('Map To Brand', self::MAP_TO_BRAND)->sortable()
            ])->dependsOn('type', FeedDealerIncomingMapping::MANUFACTURER_BRAND),

            Text::make('Dealer ID', 'dealer_id')->sortable(),

            Select::make('Type', 'type')
                ->options(FeedDealerIncomingMapping::$types)
                ->displayUsingLabels(),

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
            new DealerIDMapping
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
        return [];
    }
}
