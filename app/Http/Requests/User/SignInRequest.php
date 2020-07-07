<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class SignInRequest extends Request {
    
    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|string'
    ];
    
}
