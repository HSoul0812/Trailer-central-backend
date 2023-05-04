<?php

namespace App\Http\Requests\WebsiteUser;

use App\Http\Requests\Request;

class AuthenticateUserRequest extends Request implements AuthenticateRequestInterface
{
    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required|string',
        'captcha' => 'required|string',
    ];
}
