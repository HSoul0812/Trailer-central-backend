<?php

namespace App\Nova\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;

class FeedApiUploadTrendMetric extends Trend
{
    /**
     * Title of the Metric
     *
     * @return string
     */
    public function name(): string
    {
        return 'Uploads Trend';
    }

    /**
     * The width of the card (1/3, 2/3, 1/2, 1/4, 3/4, or full).
     *
     * @var string
     */
    public $width = '2/3';

    /**
     * Calculate the value of the metric.
     *
     * @param NovaRequest $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->countByDays($request, \App\Models\Feed\Uploads\FeedApiUpload::class)->showLatestValue();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            15 => '15 Days',
            30 => '30 Days',
            90 => '90 Days',
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'feed-api-upload-trend';
    }
}
