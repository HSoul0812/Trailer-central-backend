<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use App\Models\User\Interfaces\PermissionsInterface;
use App\Rules\Shared\EmailValidation;
use Illuminate\Validation\Rule;

/**
 * Class CreateSecondaryUserRequest
 *
 * @package App\Http\Requests\User
 */
class CreateSecondaryUserRequest extends Request
{
    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    protected function getRules(): array
    {
        return [
            'dealer_id' => [
                'integer',
                'min:1',
                'required',
                'exists:dealer,dealer_id',
            ],
            'email' => [
                'required',
                'unique:dealer',
                'unique:dealer_users',
                new EmailValidation(),
            ],
            'password' => [
                'required',
                'string',
            ],
            'user_permissions' => [
                'required',
                'array',
            ],
            'user_permissions.*.permission_level' => [
                'permission_level_valid:user_permissions.*.feature',
            ],
            'user_permissions.*.feature' => [
                Rule::in(PermissionsInterface::FEATURES),
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.unique' => 'The user ":input" could not be added or updated, because it is already associated '
                . 'with an existing TrailerCentral login. If problems persist, please contact support.',
        ];
    }
}
