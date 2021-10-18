<?php


namespace App\Http\Requests\Website\User;


use App\Http\Requests\Request;

class LoginRequest extends Request
{
    protected $rules = [
        'email' => 'required|email',
        'password' => 'required'
    ];
}
