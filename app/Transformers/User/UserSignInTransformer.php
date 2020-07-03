<?php

namespace App\Transformers\User;

use League\Fractal\TransformerAbstract;

class UserSignInTransformer extends TransformerAbstract 
{
    public function transform($user)
    {
	return [
             'access_token' => $user->access_token
        ];
    }
}
