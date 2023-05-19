<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class ListTTDealersRequest extends Request
{
    protected $rules = [
        'state' => 'string',
        'type' => 'integer',
    ];
}
