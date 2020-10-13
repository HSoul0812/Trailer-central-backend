<?php

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;

class UpdateSalesAuthRequest extends Request {

    protected $rules = [
        'id' => 'required|integer',
        'token_type' => 'required|valid_token_type',
        'access_token' => 'required|string',
        'id_token' => 'required|string',
        'issued_at' => 'integer',
        'expires_at' => 'integer',
        'scopes' => 'required|array',
        'scopes.*' => 'required|string|max:80',
        'smtp_email' => 'email'
    ];

}
