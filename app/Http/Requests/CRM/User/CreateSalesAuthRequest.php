<?php

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;

class CreateSalesAuthRequest extends Request {

    protected $rules = [
        'token_type' => 'required|valid_token_type',
        'access_token' => 'required|string',
        'refresh_token' => 'string',
        'id_token' => 'string',
        'issued_at' => 'date_format:Y-m-d H:i:s',
        'expires_at' => 'date_format:Y-m-d H:i:s',
        'expires_in' => 'integer',
        'scopes' => 'array',
        'scopes.*' => 'string|max:80',
        'first_name' => 'string',
        'last_name' => 'string',
        'email' => 'email',
        'perms' => 'in:admin,user',
        'is_default' => 'boolean',
        'is_inventory' => 'boolean',
        'is_financing' => 'boolean',
        'is_trade' => 'boolean',
        'signature' => 'nullable|string',
        'dealer_location_id' => 'nullable|dealer_location_valid',
        'smtp' => 'array',
        'smtp.email' => 'nullable|email',
        'smtp.password' => 'nullable|string',
        'smtp.server' => 'nullable|string',
        'smtp.port' => 'nullable|integer',
        'smtp.security' => 'nullable|sales_security_type',
        'smtp.auth' => 'nullable|sales_auth_type',
        'smtp.failed' => 'nullable|boolean',
        'imap' => 'array',
        'imap.email' => 'nullable|email',
        'imap.password' => 'nullable|string',
        'imap.server' => 'nullable|string',
        'imap.port' => 'nullable|integer',
        'imap.security' => 'nullable|sales_security_type',
        'imap.failed' => 'nullable|boolean',
        'folders' => 'array',
        'folders.*' => 'string'
    ];

}
