<?php

declare(strict_types=1);

namespace App\Transformers\CRM\User;

use App\Models\CRM\User\Employee;
use League\Fractal\Resource\Primitive;
use League\Fractal\TransformerAbstract;

class EmployeeTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [];

    protected $availableIncludes = ['timeClock'];

    public function transform(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'display_name' => $employee->display_name,
            'dealer_user_id' => $employee->crm_user_id,
            'service_user_id' => $employee->service_user_id
        ];
    }

    public function includeTimeClock(Employee $employee): Primitive
    {
        return $this->primitive($employee->timeClock, $employee->timeClock ? new TimeClockTransformer() : null);
    }
}
