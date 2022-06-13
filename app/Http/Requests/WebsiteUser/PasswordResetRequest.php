<?php

namespace App\Http\Requests\WebsiteUser;

use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Request;

class PasswordResetRequest extends Request implements PasswordResetRequestInterface
{
    protected array $rules = [
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ];
}
