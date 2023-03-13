<?php

namespace App\Nova\Metrics\TransactionExecute;

use App\Models\Feed\TransactionExecuteQueue;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * class TrailerWorldMetric
 *
 * @package App\Nova\Metrics\TransactionExecute
 */
class TrailerWorldMetric extends Value
{
    /**
     * Title of the Metric
     *
     * @return string
     */
    public function name(): string
    {
        return 'Trailer World';
    }

    /**
     * The width of the card (1/3, 2/3, 1/2, 1/4, 3/4, or full).
     *
     * @var string
     */
    public $width = '1/3';

    /**
     * Calculate the value of the metric.
     *
     * @param NovaRequest $request
     * @return ValueResult
     */
    public function calculate(NovaRequest $request): ValueResult
    {
        return $this->count($request, TransactionExecuteQueue::where('api', 'trailerworld'));
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges(): array
    {
        return [
            'TODAY' => 'Today',
            1 => 'Yesterday',
            7 => '7 Days',
            14 => '14 Days',
            28 => '28 Days',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'transaction-execute-trailer-world';
    }
}
