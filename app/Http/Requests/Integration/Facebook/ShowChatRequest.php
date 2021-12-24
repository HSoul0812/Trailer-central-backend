<?php

namespace App\Http\Requests\Integration\Facebook;

use App\Http\Requests\Request;

/**
 * Show Facebook Chat Request
 * 
 * @author David A Conway Jr.
 */
class ShowChatRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];

}
