<?php

namespace App\Http\Requests\WebsiteUser;

use App\Http\Requests\Request;
use Illuminate\Validation\Rules\Password;

class PasswordResetRequest extends Request implements PasswordResetRequestInterface
{
    protected function getRules(): array
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }
}
