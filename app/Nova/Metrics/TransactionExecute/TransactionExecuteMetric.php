<?php

namespace App\Nova\Metrics\TransactionExecute;

use App\Models\Feed\TransactionExecuteQueue;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

/**
 * class TransactionExecuteMetric
 *
 * @package App\Nova\Filters\TransactionExecute
 */
class TransactionExecuteMetric extends Partition
{
    /**
     * Title of the Metric
     *
     * @return string
     */
    public function name(): string
    {
        return 'Transactions Metric';
    }

    /**
     * The width of the card (1/3, 2/3, 1/2, 1/4, 3/4, or full).
     *
     * @var string
     */
    public $width = '1/2';

    /**
     * Calculate the value of the metric.
     *
     * @param NovaRequest $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->count($request, TransactionExecuteQueue::class, 'api');
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
        return 'transaction-execute-queue-metric';
    }
}
