<?php

namespace App\Http\Requests\Dms\Printer;

use App\Http\Requests\Request;

class GetOkidataRequest extends Request
{
    protected $rules = [
        'name' => 'string',
        'region' => 'string|min:2',
        'department' => 'string',
        'division' => 'string',
        'search_term' => 'string|min:2',
    ];

}