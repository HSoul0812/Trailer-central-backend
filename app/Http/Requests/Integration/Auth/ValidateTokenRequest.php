<?php

namespace App\Http\Requests\Integration\Auth;

use App\Http\Requests\Request;

/**
 * Validate Token Request
 * 
 * @author David A Conway Jr.
 */
class ValidateTokenRequest extends Request {

    protected $rules = [
        'token_type' => 'required|valid_token_type',
        'access_token' => 'required|string',
        'refresh_token' => 'string',
        'id_token' => 'string',
        'issued_at' => 'date_format:Y-m-d H:i:s',
        'expires_at' => 'date_format:Y-m-d H:i:s',
        'scopes' => 'array',
        'scopes.*' => 'string|max:80'
    ];
}