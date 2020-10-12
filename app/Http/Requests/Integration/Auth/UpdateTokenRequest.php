<?php

namespace App\Http\Requests\Integration\Auth;

use App\Http\Requests\Request;

/**
 * Update Token Request
 * 
 * @author David A Conway Jr.
 */
class UpdateTokenRequest extends Request {
    
    protected $rules = [
        'id' => 'integer',
        'token_type' => 'valid_token_type',
        'relation_type' => 'valid_relation_type',
        'relation_id' => 'valid_relation_id',
        'access_token' => 'string|max:255',
        'id_token' => 'string',
        'issued_at' => 'integer',
        'expires_at' => 'integer',
        'scopes' => 'array',
        'scopes.*' => 'string|max:80'
    ];
    
}