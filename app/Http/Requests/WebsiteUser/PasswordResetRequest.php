<?php

namespace App\Http\Requests\WebsiteUser;

use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Request;

class PasswordResetRequest extends Request implements CreateRequestInterface
{
    protected array $rules = [
        'email' => 'required|email'
    ];
}
