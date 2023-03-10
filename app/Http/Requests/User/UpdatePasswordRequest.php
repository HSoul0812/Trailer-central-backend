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
            'min:8',
        ],
    ];

    public function messages(): array
    {
        return [
            'password.min' => 'The :attribute should be at least :min characters.',
            'password.regex' => 'The :attribute should have at least 1 Capital Letter, 1 Small Letter, 1 Digit and 1 symbol [Allowed Symbols - @$!%*#?&_]'
        ];
    }
}
