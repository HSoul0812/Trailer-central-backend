<?php

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;

class LoginSalesAuthRequest extends Request {

    protected $rules = [
        'token_type' => 'required|valid_token_type',
        'sales_person_id' => 'nullable|integer',
        'redirect_uri' => 'nullable|string',
        'scopes' => 'nullable|array',
        'scopes.*' => 'string|max:80',
        'first_name' => 'nullable|string',
        'last_name' => 'nullable|string',
        'email' => 'nullable|email',
        'perms' => 'nullable|in:admin,user',
        'is_default' => 'nullable|boolean',
        'is_inventory' => 'nullable|boolean',
        'is_financing' => 'nullable|boolean',
        'is_trade' => 'nullable|boolean',
        'signature' => 'nullable|string',
        'dealer_location_id' => 'nullable|dealer_location_valid'
    ];

}