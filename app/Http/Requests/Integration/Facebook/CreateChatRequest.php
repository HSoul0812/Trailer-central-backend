<?php

namespace App\Http\Requests\Integration\Facebook;

use App\Http\Requests\Request;

/**
 * Create Chat Request
 * 
 * @author David A Conway Jr.
 */
class CreateChatRequest extends Request {

    protected $rules = [
        'user_id' => 'required|integer',
        'sales_person_id' => 'integer',
        'account_id' => 'required|integer',
        'account_name' => 'required|string',
        'page_id' => 'required|integer',
        'access_token' => 'required|string|max:255',
        'id_token' => 'string',
        'issued_at' => 'date_format:Y-m-d H:i:s',
        'expires_at' => 'date_format:Y-m-d H:i:s',
        'expires_in' => 'integer',
        'scopes' => 'required|array',
        'scopes.*' => 'required|string|max:80',
        'is_active' => 'nullable|boolean',
        'filters' => 'nullable|json'
    ];
}