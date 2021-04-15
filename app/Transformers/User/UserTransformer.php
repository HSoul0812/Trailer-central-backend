<?php

namespace App\Transformers\User;

use League\Fractal\TransformerAbstract;
use App\Models\User\User;

class UserTransformer extends TransformerAbstract 
{
    
    private const PROFILE_IMAGE = 'https://static-trailercentral.s3.amazonaws.com/files/Screen+Shot+2021-04-15+at+4.02.05+PM.png';
    
    public function transform(User $user)
    {
	 return [
             'id' => $user->dealer_id,
             'created_at' => $user->created_at,
             'name' => $user->name,
             'profile_image' => self::PROFILE_IMAGE
         ];
    }
}
