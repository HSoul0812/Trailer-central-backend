<?php


namespace App\Http\Requests\Website;


use App\Http\Requests\Request;

class CreateUserRequest extends Request
{
    protected $rules = [
        'first_name' => 'required',
        'middle_name' => 'nullable',
        'last_name' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8'
    ];
}
