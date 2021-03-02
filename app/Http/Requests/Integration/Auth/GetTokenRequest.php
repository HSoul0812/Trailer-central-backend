<?php

namespace App\Http\Requests\Integration\Auth;

use App\Http\Requests\Request;

/**
 * Get Token Request
 * 
 * @author David A Conway Jr.
 */
class GetTokenRequest extends Request {
    
    protected $rules = [
        'token_type' => 'required|valid_token_type',
        'relation_type' => 'required|valid_relation_type',
        'relation_id' => 'required|valid_relation_id'
    ];
}
