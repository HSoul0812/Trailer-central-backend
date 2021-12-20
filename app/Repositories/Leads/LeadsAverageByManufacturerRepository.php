<?php

declare(strict_types=1);

namespace App\Repositories\Leads;

use App\Repositories\AbstractAverageByManufacturerRepository;

class LeadsAverageByManufacturerRepository extends AbstractAverageByManufacturerRepository implements LeadsAverageByManufacturerRepositoryInterface
{
    protected function getPerDayViewName(): string
    {
        return 'leads_average_per_day';
    }

    protected function getPerWeekViewName(): string
    {
        return 'leads_average_per_week';
    }

    protected function getPerMonthViewName(): string
    {
        return 'leads_average_per_month';
    }

    protected function getPerQuarterViewName(): string
    {
        return 'leads_average_per_quarter';
    }

    protected function getPerYearViewName(): string
    {
        return 'leads_average_per_year';
    }
}
