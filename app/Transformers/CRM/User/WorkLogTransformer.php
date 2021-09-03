<?php

declare(strict_types=1);

namespace App\Transformers\CRM\User;

use App\Models\CRM\User\Employee;
use Illuminate\Support\Facades\Date;
use League\Fractal\TransformerAbstract;

class WorkLogTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [];

    protected $availableIncludes = [];

    public function transform(Employee $employee): array
    {
        return [
            'ro_id' => $employee->user_defined_id,
            'date' => Date::parse($employee->start_date)->format('Y-m-d'),
            'start' => $employee->start_date,
            'end' => $employee->completed_date,
            'labor_code' => $employee->labor_code,
            'hourly_rate' => $employee->hourly_rate,
            'billed_hours' => $employee->billed_hrs,
            'paid_hours' => $employee->paid_hrs,
        ];
    }
}
