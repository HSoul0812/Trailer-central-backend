<?php
namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class CreateUserRequest extends Request
{
    protected $rules = [
        'name' => 'required',
        'email' => 'required|email',
        'password' => 'required|string|min:8',
    ];
}
