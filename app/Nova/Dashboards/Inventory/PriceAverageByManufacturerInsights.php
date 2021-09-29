<?php

declare(strict_types=1);

namespace App\Nova\Dashboards\Inventory;

use App\Nova\Http\Requests\Inventory\PriceAverageRequest;
use App\Nova\Http\Requests\Inventory\PriceAverageRequestInterface;
use App\Services\Inventory\PriceAverageByManufacturerServiceInterface;

class PriceAverageByManufacturerInsights extends AbstractAverageByManufacturerInsights
{
    public function __construct(private PriceAverageByManufacturerServiceInterface $service)
    {
        $this->constructRequestBindings();

        parent::__construct($this->service, self::uriKey());
    }

    /**
     * Get the URI key for the metric.
     */
    public static function uriKey(): string
    {
        return 'price-average-by-manufacturer-insights';
    }

    public static function label(): string
    {
        return 'Price AVG by manufacturer';
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(
            PriceAverageRequestInterface::class,
            fn () => inject_request_data(PriceAverageRequest::class)
        );

        app()->bindMethod(
            __CLASS__ . '@cards',
            fn (self $class) => $class->cards(app()->make(PriceAverageRequestInterface::class))
        );
    }
}
