<?php

namespace App\Http\Requests\WebsiteUser;

use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Request;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends Request implements CreateRequestInterface
{
    protected function getRules(): array
    {
        return [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'address' => 'nullable|string',
            'zipcode' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'email' => 'email|required|unique:App\Models\WebsiteUser\WebsiteUser,email',
            'phone_number' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'mobile_number' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'password' => ['required', 'confirmed', Password::min(8)],
            'captcha' => 'required|string',
        ];
    }
}
