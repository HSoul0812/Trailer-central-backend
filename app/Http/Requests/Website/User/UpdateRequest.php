<?php


namespace App\Http\Requests\Website\User;


use App\Http\Requests\Request;

class UpdateRequest extends Request
{
    protected $rules = [
        'first_name' => 'required',
        'middle_name' => 'nullable',
        'last_name' => 'required',
        'password' => 'min:8',
    ];
}
