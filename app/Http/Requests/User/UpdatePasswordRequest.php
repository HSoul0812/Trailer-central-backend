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
        'password' => 'required|min:6'
    ];
}
