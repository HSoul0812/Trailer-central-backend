<?php

namespace App\Transformers\User;

use App\Models\User\User;
use App\Transformers\User\DealerLocationSimpleTransformer;
use League\Fractal\TransformerAbstract;

class DealerOfTTTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'locations',
    ];

    public function transform($user): array
    {
        return [
            'id' => $user->dealer_id,
            'identifier' => $user->identifier,
            'created_at' => $user->created_at,
            'name' => $user->name,
            'email' => $user->email,
            'clsf_active' => $user->clsf_active,
        ];
    }

    public function includeLocations($user)
    {
        return $this->collection($user->locations, new DealerLocationSimpleTransformer());
    }
}
