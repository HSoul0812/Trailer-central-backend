<?php

namespace App\Transformers\User;

use App\Models\User\DealerUser;
use App\Models\User\DealerUserPermission;
use App\Models\User\User;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
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
             'primary_email' => $user->user ? $user->user->email : $user->email,
             'is_crm_active' => $user->is_crm_active ?? $user->user->is_crm_active,
             'is_parts_active' => $user->is_parts_active ?? $user->user->is_parts_active,
             'is_marketing_active' => $user->is_marketing_active ?? $user->user->is_marketing_active,
             'profile_image' => config('user.profile.image'),
             'website' => $user->website
        ];
    }

    public function includePermissions($user): Collection
    {
        return $this->collection($this->getUserPermissions($user), new DealerPermissionsTransformer());
    }

    /**
     * @param User|DealerUser $user
     * @return \Illuminate\Support\Collection|array<DealerUserPermission>
     */
    private function getUserPermissions($user)
    {
        if ($user instanceof DealerUser) {
            return $user->getPermissions();
        }

        return $user->getPermissionsAllowed();
    }
}
