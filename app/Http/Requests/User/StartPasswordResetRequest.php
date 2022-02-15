<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class StartPasswordResetRequest extends Request {
    
    protected $rules = [
        'email' => 'required|email'
    ];
    
}
