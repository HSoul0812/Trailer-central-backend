<?php

declare(strict_types=1);

namespace App\Repositories\Inventory;

use App\Repositories\AbstractAverageByManufacturerRepository;

class PriceAverageByManufacturerRepository extends AbstractAverageByManufacturerRepository implements PriceAverageByManufacturerRepositoryInterface
{
    protected function getPerDayViewName(): string
    {
        return 'inventory_price_average_per_day';
    }

    protected function getPerWeekViewName(): string
    {
        return 'inventory_price_average_per_week';
    }

    protected function getPerMonthViewName(): string
    {
        return 'inventory_price_average_per_month';
    }

    protected function getPerQuarterViewName(): string
    {
        return 'inventory_price_average_per_quarter';
    }

    protected function getPerYearViewName(): string
    {
        return 'inventory_price_average_per_year';
    }
}
