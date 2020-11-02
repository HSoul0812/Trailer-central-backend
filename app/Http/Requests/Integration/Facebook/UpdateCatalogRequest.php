<?php

namespace App\Http\Requests\Integration\Facebook;

use App\Http\Requests\Request;

/**
 * Update Catalog Request
 * 
 * @author David A Conway Jr.
 */
class UpdateCatalogRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer',
        'access_token' => 'string|max:255',
        'refresh_token' => 'nullable|string',
        'id_token' => 'string',
        'issued_at' => 'date_format:Y-m-d H:i:s',
        'expires_at' => 'date_format:Y-m-d H:i:s',
        'expires_in' => 'integer',
        'scopes' => 'array',
        'scopes.*' => 'string|max:80',
        'account_name' => 'string',
        'user_id' => 'integer',
        'filters' => 'string'
    ];
    
}