<?php

namespace App\Http\Requests\Dms\Printer;

use App\Http\Requests\Request;

class ShowFormRequest extends Request
{
    protected $rules = [
        'id' => 'required|int'
    ];

} 
