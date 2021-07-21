<?php

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;

class LoginSalesAuthRequest extends Request {

    protected $rules = [
        'token_type' => 'nullable|valid_token_type',
        'auth_code' => 'nullable|string',
        'sales_person_id' => 'nullable|integer',
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'email' => 'required|email',
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
        'folders.*.id' => 'int',
        'folders.*.name' => 'string'
    ];

}
