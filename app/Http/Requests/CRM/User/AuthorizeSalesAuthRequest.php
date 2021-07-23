<?php

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;

class AuthorizeSalesAuthRequest extends Request {

    protected $rules = [
        'auth_code' => 'required|string',
        'state' => 'nullable|string',
        'redirect_uri' => 'nullable|string',
        'scopes' => 'nullable|array',
        'scopes.*' => 'string|max:80',
    ];

}
