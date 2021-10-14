<?php


namespace App\Http\Requests\Website\User;


use App\Http\Requests\Request;

class CreateRequest extends Request
{
    protected $rules = [
        'first_name' => 'required',
        'middle_name' => 'nullable',
        'last_name' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8'
    ];
}
