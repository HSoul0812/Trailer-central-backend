<?php

namespace App\Transformers\User;

use League\Fractal\TransformerAbstract;
use App\Models\User\User;

class UserTransformer extends TransformerAbstract 
{
    public function transform(User $user)
    {
	 return [
             'id' => $user->dealer_id,
             'created_at' => $user->created_at,
             'name' => $user->name
         ];
    }
}
