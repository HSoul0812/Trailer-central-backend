<?php

namespace App\Http\Requests\Integration\Auth;

use App\Http\Requests\Request;

/**
 * Show Token Request
 * 
 * @author David A Conway Jr.
 */
class ShowTokenRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer',
        'token_type' => 'valid_token_type',
        'relation_type' => 'valid_relation_type',
        'relation_id' => 'valid_relation_id',
    ];
    
}
