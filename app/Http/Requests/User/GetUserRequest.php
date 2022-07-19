<?php
namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class GetUserRequest extends Request
{
    protected $rules = [
        'email' => 'required|email'
    ];
}
