<?php

declare(strict_types=1);

namespace App\Nova\Dashboards\Leads;

use App\Nova\Dashboards\AbstractAverageByManufacturerInsights;
use App\Nova\Http\Requests\InsightRequestInterface;
use App\Nova\Http\Requests\Leads\LeadsAverageRequest;
use App\Nova\Http\Requests\Leads\LeadsAverageRequestInterface;
use App\Services\Leads\LeadsAverageByManufacturerServiceInterface;

class LeadsAverageByManufacturerInsights extends AbstractAverageByManufacturerInsights
{
    public function __construct(private LeadsAverageByManufacturerServiceInterface $service)
    {
        $this->constructRequestBindings();

        parent::__construct($this->service, self::uriKey());
    }

    /**
     * Get the URI key for the metric.
     */
    public static function uriKey(): string
    {
        return 'leads-average-by-manufacturer-insights';
    }

    public static function label(): string
    {
        return 'Leads AVG by manufacturer';
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(
            LeadsAverageRequestInterface::class,
            fn () => inject_request_data(LeadsAverageRequest::class)
        );

        app()->bind(InsightRequestInterface::class, LeadsAverageRequestInterface::class);
    }
}
