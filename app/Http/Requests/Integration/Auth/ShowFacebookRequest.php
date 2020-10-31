<?php

namespace App\Http\Requests\Integration\Auth;

use App\Http\Requests\Request;

/**
 * Show Facebook Request
 * 
 * @author David A Conway Jr.
 */
class ShowFacebookRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];

}
