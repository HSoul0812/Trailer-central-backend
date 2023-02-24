<?php

namespace App\Nova\Resources\Integration;

use App\Nova\Resource;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\KeyValue;

use App\Models\Feed\TransactionExecuteQueue as TEQ;

class TransactionExecuteQueue extends Resource
{
    public static $group = 'Factory Feeds';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = TEQ::class;

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
        'api',
        'data'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request): array {
        return [
            ID::make(),

            Text::make('Api')->sortable(),

            Text::make('VIN', 'data')->displayUsing(function($value) {
                return $value['vin'] ?? null;
            })->onlyOnIndex(),

            Text::make('Stock', 'data')->displayUsing(function($value) {
                return $value['stock_id'] ?? null;
            })->onlyOnIndex(),

            Code::make('Data')->language('javascript')->json()->hideFromIndex(),

            Text::make('Response')->sortable(),

            Text::make('Operation Type')->sortable(),

            DateTime::make('Queued At')->sortable(),
            DateTime::make('Executed At')->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request): array {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request): array {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request): array {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request): array {
        return [];
    }
}
