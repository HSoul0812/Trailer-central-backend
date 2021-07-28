<?php

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;

class GetTimeClockStatusRequest extends Request {

    protected $rules = [
        'user_id' => 'integer|min:1|required|exists:new_user,user_id',
    ];
}
