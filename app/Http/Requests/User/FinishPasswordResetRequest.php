<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class FinishPasswordResetRequest extends Request
{
    protected $rules = [
        'code' => 'required',
        'password' => ['required', 'min:6', 'max:8']
    ];

    public function messages(): array
    {
        return [
            'password.min' => 'The :attribute should be at least :min characters.',
            'password.max' => 'The :attribute should not be greater than :max characters.',
        ];
    }
}
