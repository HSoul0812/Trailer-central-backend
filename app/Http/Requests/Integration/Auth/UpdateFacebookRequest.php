<?php

namespace App\Http\Requests\Integration\Auth;

use App\Http\Requests\Request;

/**
 * Update Facebook Request
 * 
 * @author David A Conway Jr.
 */
class UpdateFacebookRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer',
        'token_type' => 'valid_token_type',
        'access_token' => 'string|max:255',
        'refresh_token' => 'string',
        'id_token' => 'string',
        'issued_at' => 'date_format:Y-m-d H:i:s',
        'expires_at' => 'date_format:Y-m-d H:i:s',
        'scopes' => 'array',
        'scopes.*' => 'string|max:80',
        'account_name' => 'string',
        'account_id' => 'string'
    ];
    
}