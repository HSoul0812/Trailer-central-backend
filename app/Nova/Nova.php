<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Support\Collection;
use Laravel\Nova\Http\Requests\NovaRequest;

class Nova extends \Laravel\Nova\Nova
{
    /**
     * Get the available dashboard cards for the given request.
     *
     * @param string $dashboard
     */
    public static function availableDashboardCardsForDashboard($dashboard, NovaRequest $request): Collection
    {
        return collect(static::$dashboards)
            ->filter->authorize($request)
            ->filter(fn ($dash) => $dash->uriKey() === $dashboard)
            ->flatMap(fn ($dashboard) => app()->call([$dashboard, 'cards']))
            ->filter->authorize($request)
            ->values();
    }

    /**
     * Get the available dashboard cards for the given request.
     */
    public static function allAvailableDashboardCards(NovaRequest $request): Collection
    {
        return collect(static::$dashboards)
            ->filter->authorize($request)
            ->flatMap(fn ($dashboard) => $dashboard->cards())
            ->merge(static::$cards)
            ->unique()
            ->filter->authorize($request)
            ->values();
    }
}
