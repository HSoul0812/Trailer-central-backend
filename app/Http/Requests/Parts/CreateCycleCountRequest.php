<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class CreateCycleCountRequest extends Request {
    
    protected $rules = [
        'bin_id' => 'integer',
        'dealer_id' => 'integer',
        'is_completed' => 'boolean',
        'is_balanced' => 'boolean',
        'parts' => 'array|required'
    ];
    
}
