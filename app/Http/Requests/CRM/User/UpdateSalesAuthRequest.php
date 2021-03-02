<?php

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;

class UpdateSalesAuthRequest extends Request {

    protected $rules = [
        'id' => 'required|integer',
        'token_type' => 'required|valid_token_type',
        'access_token' => 'required|string',
        'refresh_token' => 'string',
        'id_token' => 'string',
        'issued_at' => 'date_format:Y-m-d H:i:s',
        'expires_at' => 'date_format:Y-m-d H:i:s',
        'expires_in' => 'integer',
        'scopes' => 'array',
        'scopes.*' => 'string|max:80'
    ];

}
