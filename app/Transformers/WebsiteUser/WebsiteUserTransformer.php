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
            'registration_source' => $user->registration_source,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ];
    }
}
