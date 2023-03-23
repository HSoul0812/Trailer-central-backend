<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use App\Models\User\DealerUser;
use App\Models\User\Interfaces\PermissionsInterface;
use App\Rules\Shared\EmailValidation;
use Illuminate\Validation\Rule;
use App\Rules\IsPasswordValid;

/**
 * Class UpdateSecondaryUsersRequest
 *
 * @package App\Http\Requests\User
 */
class UpdateSecondaryUsersRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'dealer_id' => [
                'integer',
                'min:1',
                'required',
                'exists:dealer,dealer_id',
            ],
            'users' => [
                'array',
            ],
            'users.*.dealer_user_id' => [
                'integer',
                'min:1',
                'required',
                'exists:dealer_users,dealer_user_id',
            ],
            'users.*.email' => [
                'nullable',
                'distinct',
                new EmailValidation(),
            ],
            'users.*.password' => [
                'nullable',
                new IsPasswordValid(),
            ],
            'users.*.user_permissions' => [
                'array',
            ],
            'users.*.user_permissions.*.permission_level' => [
                'permission_level_valid:users.*.user_permissions.*.feature',
            ],
            'users.*.user_permissions.*.feature' => [
                Rule::in(PermissionsInterface::FEATURES),
            ],
        ];
    }

    public function validate(): bool
    {
        $valid = parent::validate();

        if (!$valid) {
            return false;
        }

        // check if users belong to dealer

        foreach ($this->users as $user) {
            $dealerUser = DealerUser::findOrFail($user['dealer_user_id']);
            if ($dealerUser->dealer_id != $this->dealer_id) {
                return false;
            }
        }

        return true;
    }
}
