<?php

namespace App\Http\Requests\Integration\Auth;

use App\Http\Requests\Request;

/**
 * Show Auth Request
 * 
 * @author David A Conway Jr.
 */
class ShowAuthRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];
    
}
