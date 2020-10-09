<?php

namespace App\Http\Requests\Integration\Auth;

use App\Http\Requests\Request;

/**
 * Update Auth Request
 * 
 * @author David A Conway Jr.
 */
class UpdateAuthRequest extends Request {
    
    protected $rules = [
        'id' => 'integer',
        'token_type' => 'token_type_valid',
        'relation_type' => 'relation_type_valid',
        'relation_id' => 'relation_valid',
        'access_token' => 'string|max:255',
        'id_token' => 'string|max:255',
        'issued_at' => 'integer',
        'expires_at' => 'integer'
    ];
    
}