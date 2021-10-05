<?php

declare(strict_types=1);

namespace App\Nova\Dashboards\Inventory;

use App\Nova\Dashboards\AbstractAverageByManufacturerInsights;
use App\Nova\Http\Requests\Inventory\StockAverageRequest;
use App\Nova\Http\Requests\Inventory\StockAverageRequestInterface;
use App\Services\Inventory\StockAverageByManufacturerServiceInterface;

class StockAverageByManufacturerInsights extends AbstractAverageByManufacturerInsights
{
    public function __construct(private StockAverageByManufacturerServiceInterface $service)
    {
        $this->constructRequestBindings();

        parent::__construct($this->service, self::uriKey());
    }

    /**
     * Get the URI key for the metric.
     */
    public static function uriKey(): string
    {
        return 'stock-average-by-manufacturer-insights';
    }

    public static function label(): string
    {
        return 'Stock AVG by manufacturer';
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(
            StockAverageRequestInterface::class,
            fn () => inject_request_data(StockAverageRequest::class)
        );

        app()->bindMethod(
            __CLASS__ . '@cards',
            fn (self $class) => $class->cards(app()->make(StockAverageRequestInterface::class))
        );
    }
}
