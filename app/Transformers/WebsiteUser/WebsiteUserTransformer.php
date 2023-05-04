<?php

namespace App\Transformers\WebsiteUser;

use App\Models\WebsiteUser\WebsiteUser;
use League\Fractal\TransformerAbstract;

class WebsiteUserTransformer extends TransformerAbstract
{
    public function transform(WebsiteUser $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'address' => $user->address,
            'zipcode' => $user->zipcode,
            'city' => $user->city,
            'state' => $user->state,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'mobile_number' => $user->mobile_number,
            'email_verified_at' => $user->email_verified_at,
            'registration_source' => $user->registration_source,
            'tc_user_location_id' => $user->tc_user_location_id,
            'tc_user_id' => $user->tc_user_id,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}
