<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class FinishPasswordResetRequest extends Request
{
    protected $rules = [
        'code' => 'required',
        'password' => ['required', 'min:6', 'max:8']
    ];
}
