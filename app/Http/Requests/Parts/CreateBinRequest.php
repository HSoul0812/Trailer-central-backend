<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class CreateBinRequest extends Request {
    
    protected $rules = [
        'location' => 'required|integer|cycle_count_exists',
        'bin_name' => 'required'
    ];
    
}
