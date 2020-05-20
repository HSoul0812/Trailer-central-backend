<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class UpdateCycleCountRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer',
        'is_completed' => 'boolean',
        'is_balanced' => 'boolean',
        'parts' => 'required|array'
    ];
    
}
