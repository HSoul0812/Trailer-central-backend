<?php

namespace App\Nova\Metrics\Marketing;

use App\Models\Marketing\Facebook\Marketplace;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class FmeIntegrations extends Value
{
    /**
     * Title of the Metric
     *
     * @return string
     */
    public function name(): string
    {
        return 'Integrations Added';
    }

    /**
     * The width of the card (1/3, 2/3, 1/2, 1/4, 3/4, or full).
     *
     * @var string
     */
    public $width = '1/4';


    /**
     * Calculate the value of the metric.
     *
     * @param NovaRequest $request
     * @return ValueResult
     */
    public function calculate(NovaRequest $request): ValueResult
    {
        return $this->count($request, Marketplace::class);
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
        return 'fme-integrations';
    }
}
