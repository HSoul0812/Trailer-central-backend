<?php

namespace App\Http\Requests\Integration\Auth;

use App\Http\Requests\Request;

/**
 * Authorize Token Request
 * 
 * @author David A Conway Jr.
 */
class AuthorizeTokenRequest extends Request {
    
    protected $rules = [
        'token_type' => 'required|valid_token_type',
        'relation_type' => 'nullable|valid_relation_type',
        'relation_id' => 'nullable|integer',
        'redirect_uri' => 'required|string',
        'scopes' => 'required|array',
        'scopes.*' => 'required|string|max:80',
        'auth_code' => 'required|string',
        'state' => 'nullable|string'
    ];
}
