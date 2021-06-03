<?php

namespace App\Http\Requests\User;

use App\Models\User\Interfaces\PermissionsInterface;
use App\Http\Requests\Request;

class CreateSecondaryUserRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'email' => 'required|email',
        'password' => 'required|string',
        'user_permissions' => 'required|array'        
    ];
    
    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->rules['user_permissions.*.feature'] = 'in:'.implode(',', PermissionsInterface::FEATURES);
        $this->rules['user_permissions.*.permission_level'] = 'in:'.implode(',', PermissionsInterface::PERMISSION_LEVELS);
    }
}
