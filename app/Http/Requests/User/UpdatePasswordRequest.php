<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

/**
 * @property int $dealer_id
 * @property int $dealer_user_id
 * @property string $password
 */
class UpdatePasswordRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'dealer_user_id' => 'integer|min:1|exists:dealer_users,dealer_user_id',
        'password' => [
            'required',
            'string',
            'min:6',
            'max:8',
            'regex:/[a-z]/',      // must contain at least one lowercase letter
            'regex:/[A-Z]/',      // must contain at least one uppercase letter
            'regex:/[0-9]/',      // must contain at least one digit
            'regex:/[@$!%*#?&_]/', // must contain a special character
        ],
    ];

    public function messages(): array
    {
        return [
            'password.min' => 'The :attribute should be at least :min characters.',
            'password.max' => 'The :attribute should not be greater than :max characters.',
            'password.regex' => 'The :attribute should have at least 1 Capital Letter, 1 Small Letter, 1 Digit and 1 symbol [Allowed Symbols - @$!%*#?&_]'
        ];
    }
}
