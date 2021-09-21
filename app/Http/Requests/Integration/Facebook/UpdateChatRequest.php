<?php

namespace App\Http\Requests\Integration\Facebook;

use App\Http\Requests\Request;

/**
 * Update Chat Request
 * 
 * @author David A Conway Jr.
 */
class UpdateChatRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer',
        'user_id' => 'integer',
        'sales_person_id' => 'integer',
        'page_title' => 'string',
        'page_id' => 'integer',
        'page_token' => 'nullable|string|max:255',
        'page_refresh_token' => 'nullable|string|max:255',
        'access_token' => 'string|max:255',
        'refresh_token' => 'nullable|string|max:255',
        'id_token' => 'string',
        'issued_at' => 'date_format:Y-m-d H:i:s',
        'expires_at' => 'date_format:Y-m-d H:i:s',
        'expires_in' => 'integer',
        'scopes' => 'array',
        'scopes.*' => 'string|max:80',
        'is_active' => 'nullable|boolean',
        'filters' => 'nullable|json'
    ];
    
}