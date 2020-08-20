<?php

namespace App\Http\Requests\Website\Log;

use App\Http\Requests\Request;

class CreateLogRequest extends Request {
    
    protected $rules = [
        'message' => 'string|required'
    ];
    
}
