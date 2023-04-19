<?php

namespace App\Nova\Metrics\Marketing;

use App\Models\CRM\Dealer\DealerFBMOverview;
use App\Nova\Metrics\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class FmeTodayStatus extends Partition
{
    /**
     * The width of the card (1/3, 2/3, 1/2, 1/4, 3/4, or full).
     *
     * @var string
     */
    public $width = '1/4';

    /**
     * Title of the Metric
     *
     * @return string
     */
    public function name(): string
    {
        return 'Today\'s Status';
    }
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $groupBy = 'status_today';

        $results = DealerFBMOverview::getTodaysStatus($groupBy);
        $aggregationResult = $this->result($results->mapWithKeys(function ($result) use ($groupBy) {
            return $this->formatAggregateResult($result, $groupBy);
        })->all());

        return $aggregationResult->colors([
            'success' => 'green',
            'partial' => 'orange',
            'fail' => 'yellow',
            'not attempted' => 'lightgray'
        ]);
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'fme-today-status';
    }
}
