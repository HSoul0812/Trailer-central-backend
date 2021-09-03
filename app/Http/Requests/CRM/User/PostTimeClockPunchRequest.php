<?php

declare(strict_types=1);

namespace App\Http\Requests\CRM\User;

class PostTimeClockPunchRequest extends TimeClockWithPermissionValidationRequest
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'employee_id' => 'integer|min:1|required|exists:dealer_employee,id',
        'dealer_user_id' => 'integer|min:1|exists:dealer_users,dealer_user_id'
    ];
}
