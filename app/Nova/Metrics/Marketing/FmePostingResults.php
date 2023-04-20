<?php

namespace App\Nova\Metrics\Marketing;

use App\Models\Marketing\Facebook\PostingHistory;
use App\Nova\Metrics\Model;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Carbon\Carbon;

class FmePostingResults extends Partition
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
        return 'Success Rate(this week)';
    }

    /**
     * Calculate the value of the metric.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->count($request, PostingHistory::where('created_at', '>', Carbon::now()->startOfWeek()), 'type')->colors([
            'posting' => 'green',
            'error' => 'red',
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
        return 'fme-posting-results';
    }
}
