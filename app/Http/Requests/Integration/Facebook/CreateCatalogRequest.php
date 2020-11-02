<?php

namespace App\Http\Requests\Integration\Facebook;

use App\Http\Requests\Request;

/**
 * Create Catalog Request
 * 
 * @author David A Conway Jr.
 */
class CreateCatalogRequest extends Request {

    protected $rules = [
        'access_token' => 'required|string|max:255',
        'refresh_token' => 'nullable|string',
        'id_token' => 'string',
        'issued_at' => 'date_format:Y-m-d H:i:s',
        'expires_at' => 'date_format:Y-m-d H:i:s',
        'expires_in' => 'integer',
        'scopes' => 'required|array',
        'scopes.*' => 'required|string|max:80',
        'account_name' => 'required|string',
        'user_id' => 'required|integer',
        'filters' => 'string'
    ];
}