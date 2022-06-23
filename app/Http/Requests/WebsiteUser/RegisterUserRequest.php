<?php

namespace App\Http\Requests\WebsiteUser;

use App\Http\Requests\Request;
use App\Http\Requests\CreateRequestInterface;

class RegisterUserRequest extends Request implements CreateRequestInterface
{
    protected array $rules = [
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'address' => 'nullable|string',
        'zipcode' => 'nullable|string',
        'city' => 'nullable|string',
        'state' => 'nullable|string',
        'email' => 'email|required|unique:App\Models\WebsiteUser\WebsiteUser,email',
        'phone_number' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
        'mobile_number' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
        'password' => 'required|alpha_num|min:12',
    ];
}
