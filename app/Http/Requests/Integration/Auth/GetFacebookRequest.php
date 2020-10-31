<?php

namespace App\Http\Requests\Integration\Auth;

use App\Http\Requests\Request;

/**
 * Get Facebook Request
 * 
 * @author David A Conway Jr.
 */
class GetFacebookRequest extends Request {
    
    protected $rules = [
        'token_type' => 'required|valid_token_type',
        'relation_type' => 'required|valid_relation_type',
        'relation_id' => 'required|valid_relation_id'
    ];
}
