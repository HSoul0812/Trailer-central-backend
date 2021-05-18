<?php

namespace App\Http\Requests\Dms\Printer;

use App\Http\Requests\Request;

class ShowOkidataRequest extends Request
{
    protected $rules = [
        'id' => 'required|int'
    ];

} 
