<?php

namespace App\Transformers\User;

use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    private const PROFILE_IMAGE = 'https://static-trailercentral.s3.amazonaws.com/files/download+(1).png';

    protected $defaultIncludes = [
        'permissions'
    ];

    public function transform($user): array
    {
	    return [
             'id' => $user->dealer_id,
             'identifier' => $user->identifier ?? $user->user->identifier,
             'created_at' => $user->created_at ?? $user->user->created_at,
             'name' => $user->name ?? $user->user->name,
             'email' => $user->email ?? $user->user->email,
             'profile_image' => self::PROFILE_IMAGE,
             'website' => $user->website
        ];
    }

    public function includePermissions($user): Collection
    {
        return $this->collection($user->getPermissions(), new DealerPermissionsTransformer());
    }
}
