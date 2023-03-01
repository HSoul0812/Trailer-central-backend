<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class ListUserByNameRequest extends Request
{
    protected $rules = [
        'name' => 'required|string',
    ];
}
