<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use App\Rules\IsPasswordValid;

/**
 * @property int $dealer_id
 * @property int $dealer_user_id
 * @property string $password
 */
class UpdatePasswordRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
            'dealer_user_id' => 'integer|min:1|exists:dealer_users,dealer_user_id',
            'password' => [
                'required',
                new IsPasswordValid(),
            ],
            'current_password' => [
                'required',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.min' => 'The :attribute should be at least :min characters.',
        ];
    }
}
