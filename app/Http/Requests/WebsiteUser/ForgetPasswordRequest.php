<?php

namespace App\Http\Requests\WebsiteUser;

use App\Http\Requests\Request;

class ForgetPasswordRequest extends Request implements ForgetPasswordRequestInterface
{
    protected array $rules = [
        'email' => 'required|email',
        'callback' => 'url',
        'captcha' => 'required|string',
    ];
}
