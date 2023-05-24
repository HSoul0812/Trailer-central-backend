<?php

namespace App\Transformers\User;

use App\Models\User\User;
use League\Fractal\TransformerAbstract;

class DealerOfTTTransformer extends TransformerAbstract
{
    public function transform($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'clsf_active' => $user->clsf_active,
            'location_id' => $user->dealer_location_id,
            'location_name' => $user->location_name,
            'region' => $user->region,
            'city' => $user->city,
            'zip' => $user->postalcode,
        ];
    }
}
