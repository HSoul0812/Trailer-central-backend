<?php

namespace App\Http\Requests\WebsiteUser;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class AuthenticateUserRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required|string'
    ];
}
