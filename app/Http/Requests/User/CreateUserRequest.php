<?php
namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CreateUserRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'name' => 'required',
            'email' => 'required|email|unique:App\Models\User\User,email',
            'password' => 'required|alpha_num|min:12',
            'from' => 'in:trailercentral,trailertrader',
            'clsf_active' => ['required', Rule::in([0, 1])],
        ];
    }
}
