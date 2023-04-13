<?php

namespace App\Nova\Lenses\Marketing;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Lenses\Lens;

class FmePartialToday extends Lens
{
    public $name = "Integrations Partial Today";

    /**
     * Get the query builder / paginator for the lens.
     *
     * @param \Laravel\Nova\Http\Requests\LensRequest $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return mixed
     */
    public static function query(LensRequest $request, $query)
    {
        return $request->withOrdering($request->withFilters(
            $query->select([
                'id',
                'dealer',
                'posts_per_day',
                'last_attempt_ts',
                'last_attempt_posts_remaining',
                'last_known_error_type',
                'last_known_error_message',
            ])
                ->where('last_attempt_ts', '>=', date('Y-m-d 00:00:00'))
                ->where('last_attempt_posts', '>', 0)
                ->where('last_attempt_posts_remaining', '>', 0)
        ));
    }

    /**
     * Get the fields available to the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('ID', 'id'),

            Text::make('Dealer', 'dealer')
                ->sortable(),

            Text::make('Last Attempt', function () {
                if (stripos($this->last_attempt_ts, '1000') !== false) {
                    return "never";
                } else {
                    return date('M-d H:i', strtotime($this->last_attempt_ts));
                }
            })->sortable(),

            Number::make('Posts per Day', 'posts_per_day')
                ->onlyOnDetail()
                ->sortable(),

            Number::make('Remaining', 'last_attempt_posts_remaining')
                ->sortable(),

            Text::make('Last Error Code', 'last_known_error_type')
                ->sortable(),

            Text::make('Last Error Message', 'last_known_error_message'),

        ];
    }

    /**
     * Get the cards available on the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available on the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return parent::actions($request);
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'fme-partial-today';
    }
}
