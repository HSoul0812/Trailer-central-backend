<?php

declare(strict_types=1);

namespace Laravel\Nova\Http\Requests;

use App\Nova\Nova;
use Illuminate\Support\Collection;

class DashboardRequest extends NovaRequest
{
    /**
     * Get all the possible cards for the request.
     *
     * @param string $dashboard
     *
     * @noinspection PhpMissingParamTypeInspection
     */
    public function availableCards($dashboard): Collection
    {
        if ($dashboard === 'main') {
            return collect(Nova::$defaultDashboardCards)
                ->unique()
                ->filter
                ->authorize($this)
                ->values();
        }

        return Nova::availableDashboardCardsForDashboard($dashboard, $this);
    }
}
