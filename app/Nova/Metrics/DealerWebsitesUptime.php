<?php

namespace App\Nova\Metrics;

use Illuminate\Support\Facades\Redis;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class DealerWebsitesUptime extends Value
{
    public const DEALER_WEBSITES_UPTIME_KEY = 'dealer_websites_uptime';

    /**
     * Get the displayable name of the metric
     *
     * @return string
     */
    public function name()
    {
        return 'Dealer Websites Uptime %';
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $uptime = 0;

        $redis = Redis::connection('persist');

        if ($redis->keys(self::DEALER_WEBSITES_UPTIME_KEY)) {
            $uptime = $redis->get(self::DEALER_WEBSITES_UPTIME_KEY);
        }

        return $this->result($uptime)->format('0.00');
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [];
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
        return 'dealer-sites-uptime';
    }
}
