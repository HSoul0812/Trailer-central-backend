<?php

declare(strict_types=1);

namespace App\Http\Requests\Dms\ServiceOrder;

class GetMonthlyReportRequest extends GetReportRequest
{
    protected function getRules(): array
    {
        return parent::getRules() + ['sort' => 'in:month_name,-month_name,type,-type,unit_price,-unit_price'];
    }
}
