<?php

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;

class AuthSalesPeopleRequest extends Request {

    protected $rules = [
        'id' => 'required|integer',
        'token_type' => 'required|valid_token_type',
        'access_token' => 'required|string|max:255',
        'id_token' => 'string|max:255',
        'issued_at' => 'integer',
        'expires_at' => 'integer'
    ];

}
