<?php


namespace App\Http\Requests\Website;


use App\Http\Requests\Request;

class LoginUserRequest extends Request
{
    protected $rules = [
        'email' => 'required|email',
        'password' => 'required'
    ];
}
