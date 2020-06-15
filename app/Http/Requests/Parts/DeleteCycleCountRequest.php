<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class DeleteCycleCountRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer|cycle_count_exists'
    ];
    
}
