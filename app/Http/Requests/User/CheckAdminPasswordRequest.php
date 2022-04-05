<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class CheckAdminPasswordRequest extends Request
{
    protected $rules = [
        'password' => 'required'
    ];
}
