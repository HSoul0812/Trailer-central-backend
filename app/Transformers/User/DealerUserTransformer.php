<?php

namespace App\Transformers\User;

class DealerUserTransformer extends UserTransformer
{
    public function transform($user): array
    {
	    return [
             'id' => $user->dealer_user_id,
             'created_at' => $user->created_at,
             'name' => $user->email,
             'sales_person' => $user->sales_person,
        ];
    }
}
