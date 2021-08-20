<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use App\Models\User\DealerUser;
use App\Models\User\Interfaces\PermissionsInterface;

class UpdateSecondaryUsersRequest extends Request 
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'users' => 'array',
        'users.*.dealer_user_id' => 'integer|min:1|required|exists:dealer_users,dealer_user_id',
        'users.*.email' => 'nullable|email',
        'users.*.password' => 'nullable|string',
        'users.*.user_permissions' => 'required|array',
        'users.*.user_permissions.*.permission_level' => 'permission_level_valid'
    ];
    
    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->rules['users.*.user_permissions.*.feature'] = 'in:'.implode(',', PermissionsInterface::FEATURES);
    }
    
    public function validate(): bool
    {
        $valid = parent::validate();
        
        if (!$valid) {
            return false;
        }
        
        // check if users belong to dealer
        
        foreach($this->users as $user) {
            $dealerUser = DealerUser::findOrFail($user['dealer_user_id']);
            if ( $dealerUser->dealer_id != $this->dealer_id ) {
                return false;
            }
        }
        
        return true;
    }

}
