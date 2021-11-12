<?php


namespace App\Http\Requests\Website\User;


use App\Http\Requests\Request;

class UpdateRequest extends Request
{
    protected $rules = [
        'first_name' => 'nullable',
        'middle_name' => 'nullable',
        'last_name' => 'nullable',
        'current_password' => 'nullable|string',
        'new_password' => 'nullable|string|min:8',
    ];
}
