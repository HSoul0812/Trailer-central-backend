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
        'dealer_location_id' => 'required|integer',
        'account_name' => 'required|string',
        'account_id' => 'required|integer',
        'page_title' => 'required|string',
        'page_id' => 'required|integer',
        'page_token' => 'required|string|max:255',
        'refresh_token' => 'nullable|string|max:255',
        'access_token' => 'required|string|max:255',
        'refresh_token' => 'nullable|string|max:255',
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