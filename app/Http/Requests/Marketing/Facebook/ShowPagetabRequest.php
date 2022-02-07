<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;

/**
 * Show Facebook Page Tab Request
 * 
 * @author David A Conway Jr.
 */
class ShowPagetabRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];

}
