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
        'id' => 'required|integer',
        'token_type' => 'valid_token_type',
        'relation_type' => 'valid_relation_type',
        'relation_id' => 'valid_relation_id',
    ];
    
}
