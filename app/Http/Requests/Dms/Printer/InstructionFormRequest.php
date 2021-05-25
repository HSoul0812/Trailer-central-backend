<?php

namespace App\Http\Requests\Dms\Printer;

use App\Http\Requests\Request;

class InstructionFormRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|int',
        'id' => 'required|int'
    ];

}