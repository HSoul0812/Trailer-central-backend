<?php

namespace App\Http\Requests\WebsiteUser;

use App\Http\Requests\Request;
use App\Http\Requests\UpdateRequestInterface;

class UpdateUserRequest extends Request implements UpdateRequestInterface
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
            'phone_number' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'mobile_number' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
        ];
    }
}
