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
        'permissions',
        'logo'
    ];

    public function transform($user): array
    {
        // We always want to use the user's email
        // if the access-token belongs to the dealer, then we use the dealer email
        // if it belongs to the secondary user, then we use the secondary user email
        $email = $user->email;

        // We'll make sure that the $user variable is the instance
        // of the User model (the dealer)
        if ($user instanceof DealerUser) {
            $user = $user->user;
        }

        return [
            'id' => $user->dealer_id,
            'identifier' => $user->identifier,
            'created_at' => $user->created_at,
            'name' => $user->name,
            'email' => $email,
            'primary_email' => $user->email,
            'clsf_active' => $user->clsf_active,
            'is_dms_active' => $user->is_dms_active,
            'is_crm_active' => $user->is_crm_active,
            'is_parts_active' => $user->is_parts_active,
            'is_marketing_active' => $user->is_marketing_active,
            'is_fme_active' => $user->is_fme_active,
            'profile_image' => config('user.profile.image'),
            'website' => $user->website,
            'from' => $user->from,
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

    public function includeLogo($user)
    {
        $logo = $user->logo;
        return $logo ? $this->item($logo, new DealerLogoTransformer()) : $this->null();
    }
}
