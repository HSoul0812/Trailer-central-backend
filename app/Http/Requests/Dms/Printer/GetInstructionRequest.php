<?php

namespace App\Http\Requests\Dms\Printer;

use App\Http\Requests\Request;

class GetInstructionRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|required',
        'barcode_data' => 'required',
        'label' => 'required'
    ];

} 
