<?php

declare(strict_types=1);

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;

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
        throw new NotImplementedException();
    }

    protected function getPerYearViewName(): string
    {
        throw new NotImplementedException();
    }

    protected function getAggregateName(): string
    {
        return 'price';
    }
}
