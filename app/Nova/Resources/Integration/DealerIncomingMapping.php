<?php

namespace App\Nova\Resources\Integration;

use App\Models\Inventory\Attribute;
use App\Models\Inventory\Category;
use App\Models\Inventory\EntityType;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Status;
use App\Models\User\DealerLocation;
use App\Nova\Filters\Integration\IncomingMappingsTypeFilter;
use App\Nova\Resources\Dealer\Dealer;
use Epartment\NovaDependencyContainer\HasDependencies;
use Epartment\NovaDependencyContainer\NovaDependencyContainer;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use App\Models\Feed\Mapping\Incoming\DealerIncomingMapping as FeedDealerIncomingMapping;
use App\Nova\Resource;
use App\Nova\Filters\DealerIDMapping;
use Laravel\Nova\Http\Requests\NovaRequest;

class DealerIncomingMapping extends Resource
{
    use HasDependencies;

    const MAP_TO_MANUFACTURER = 'map_to_manufacturer';
    const MAP_TO_BRAND = 'map_to_brand';

    public static $group = 'Integration';

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
        $sortedTypes = FeedDealerIncomingMapping::$types;
        unset($sortedTypes[FeedDealerIncomingMapping::FIELDS]);
        unset($sortedTypes[FeedDealerIncomingMapping::DEFAULT_VALUES]);

        uasort($sortedTypes, function ($a, $b) {
            return strcmp($a, $b);
        });

        $attributes = Attribute::select('values', 'code')->get();

        $fields = [
            Select::make('Type', 'type')
                ->options($sortedTypes)
                ->rules('required')
                ->displayUsingLabels(),

            BelongsTo::make('Dealer', 'dealers', Dealer::class)->sortable()->rules('required'),

            Text::make('Map From', 'map_from')->sortable()->rules('required'),

            Text::make('Map To', 'map_to')->sortable()->exceptOnForms(),

            NovaDependencyContainer::make([
                Select::make('Map To', 'map_to')
                    ->options(Status::select('id', 'label')->orderBy('label')->get()->pluck('label', 'id'))
                    ->rules('required')
            ])->dependsOn('type', FeedDealerIncomingMapping::STATUS)->onlyOnForms(),

            NovaDependencyContainer::make([
                Text::make('Map To', 'map_to')
            ])->dependsOn('type', FeedDealerIncomingMapping::LOCATION)->onlyOnForms(),

            NovaDependencyContainer::make([Text::make('Map To', 'map_to')->sortable()->rules('required')])
                ->dependsOn('type', FeedDealerIncomingMapping::BRAND)->onlyOnForms(),

            NovaDependencyContainer::make([
                Select::make('Map To', 'map_to')
                    ->options(Inventory::CONDITION_MAPPING)
                    ->rules('required')
            ])->dependsOn('type', FeedDealerIncomingMapping::CONDITION)->onlyOnForms(),

            NovaDependencyContainer::make([
                Select::make('Map To', 'map_to')
                    ->options(EntityType::select('entity_type_id', 'title')->orderBy('title')->get()->pluck('title', 'entity_type_id'))
                    ->rules('required')
            ])->dependsOn('type', FeedDealerIncomingMapping::ENTITY_TYPE)->onlyOnForms(),

            NovaDependencyContainer::make([
                Select::make('Map To', 'map_to')
                    ->options(Category::select('legacy_category', 'label')->orderBy('label')->get()->pluck('label', 'legacy_category'))
                    ->rules('required')
            ])->dependsOn('type', FeedDealerIncomingMapping::CATEGORY)->onlyOnForms(),

            NovaDependencyContainer::make([Text::make('Map To', 'map_to')->sortable()->rules('required')])
                ->dependsOn('type', FeedDealerIncomingMapping::MAKE)->onlyOnForms(),

            NovaDependencyContainer::make([
                Text::make('Map To Manufacturer', self::MAP_TO_MANUFACTURER)->sortable()->rules('required')
            ])->dependsOn('type', FeedDealerIncomingMapping::MANUFACTURER_BRAND)->onlyOnForms(),

            NovaDependencyContainer::make([
                Text::make('Map To Brand', self::MAP_TO_BRAND)->sortable()->rules('required')
            ])->dependsOn('type', FeedDealerIncomingMapping::MANUFACTURER_BRAND)->onlyOnForms(),
        ];

        foreach ($attributes as $attribute) {
            if (!in_array($attribute->code, array_keys(FeedDealerIncomingMapping::$types))) {
                continue;
            }

            $fields[] = NovaDependencyContainer::make([
                Select::make('Map To', 'map_to')
                    ->options($attributes->firstWhere('code', $attribute->code)->getValuesArray())
                    ->rules('required')
            ])->dependsOn('type', $attribute->code)->onlyOnForms();
        }

        return $fields;
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->whereNotIn('type', [FeedDealerIncomingMapping::DEFAULT_VALUES, FeedDealerIncomingMapping::FIELDS]);
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
            new DealerIDMapping,
            new IncomingMappingsTypeFilter
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
