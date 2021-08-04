<?php

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;

class AuthorizeSalesAuthRequest extends Request {

    protected $rules = [
        'token_type' => 'required|valid_token_type',
        'sales_person_id' => 'nullable|integer',
        'auth_code' => 'required|string',
        'state' => 'nullable|string',
        'redirect_uri' => 'nullable|string',
        'scopes' => 'nullable|array',
        'scopes.*' => 'string|max:80',
        'first_name' => 'nullable|string',
        'last_name' => 'nullable|string',
        'email' => 'nullable|email',
        'perms' => 'in:admin,user',
        'is_default' => 'boolean',
        'is_inventory' => 'boolean',
        'is_financing' => 'boolean',
        'is_trade' => 'boolean',
        'signature' => 'nullable|string',
        'dealer_location_id' => 'nullable|dealer_location_valid'
    ];

}
