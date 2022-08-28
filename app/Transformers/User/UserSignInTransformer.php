<?php

namespace App\Transformers\User;

use App\Models\User\DealerUser;
use App\Models\User\User;
use League\Fractal\TransformerAbstract;

class UserSignInTransformer extends TransformerAbstract
{
    public function transform($user)
    {
        $authToken = null;
        
        // We use instanceof here just in case the code send us
        // something else that's not the type of User or DealerUser
        if ($user instanceof User) {
            $authToken = $user->authToken;
        } else if ($user instanceof DealerUser) {
            $authToken = $user->authToken;
        }
        
        return [
            'access_token' => optional($authToken)->access_token,
        ];
    }
}
