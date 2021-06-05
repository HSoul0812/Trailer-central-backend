<?php

namespace App\Transformers\User;

use App\Transformers\User\UserTransformer;
use App\Models\User\DealerUser;

class DealerUserTransformer extends UserTransformer 
{
    public function transform($user)
    {                
	return [
             'id' => $user->dealer_user_id,
             'created_at' => $user->created_at,
             'name' => $user->email,
        ];
    }
}
