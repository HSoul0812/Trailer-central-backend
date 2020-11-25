<?php

namespace App\Http\Requests\Integration\Auth;

use App\Http\Requests\Request;

/**
 * Login Token Request
 * 
 * @author David A Conway Jr.
 */
class LoginTokenRequest extends Request {
    
    protected $rules = [
        'token_type' => 'required|valid_token_type',
        'redirect_uri' => 'required|string',
        'scopes' => 'required|array',
        'scopes.*' => 'required|string|max:80'
    ];
}
