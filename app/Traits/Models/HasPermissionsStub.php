<?php

namespace App\Traits\Models;

use App\Models\User\CrmUser;
use App\Models\User\DealerClapp;
use App\Models\User\DealerUserPermission;
use App\Models\User\NewDealerUser;
use App\Models\User\Interfaces\PermissionsInterface;
use Illuminate\Support\Collection;

/**
 * Class HasPermissionsEmpty
 * @package App\Traits\Models
 */
trait HasPermissionsStub
{
    /**
     * @return Collection
     */
    public function getPermissions(): Collection
    {
        return new Collection([]);
    }

    /**
     * Returns permissions allowed for a given user
     *
     * @return Collection
     */
    public function getPermissionsAllowed(): Collection
    {
        $perms = [];
        $permissions = DealerUserPermission::where('id', '>', 0)
                                ->groupBy('feature')
                                ->get();

        foreach ($permissions as $perm) {
            if ($this->hasPermission($perm->feature, $perm->permission_level)) {
                $perm->permission_level = PermissionsInterface::SUPER_ADMIN_PERMISSION;
            } else {
                $perm->permission_level = PermissionsInterface::CANNOT_SEE_PERMISSION;
            }

            $perms[] = $perm;
        }

        return collect($perms);
    }

    /**
     * @return bool
     */
    public function hasCrmPermission(): bool
    {
        $listOfUsers = NewDealerUser::select('user_id')->where('id', $this->getDealerId());

        $query = CrmUser::whereIn('user_id', $listOfUsers)->where('active', CrmUser::STATUS_ACTIVE);

        return $query->exists();
    }

    /**
     * @return bool
     */
    public function hasMarketingPermission(): bool
    {
        return DealerClapp::where('dealer_id', $this->getDealerId())->exists();
    }

    /**
     * @param string $feature
     * @param string $permissionLevel
     * @return bool
     */
    public function hasPermission(string $feature, string $permissionLevel): bool
    {
        switch ($feature) {
            case 'crm':
                return $this->hasCrmPermission();
            case 'marketing':
                return $this->hasMarketingPermission();
            // more permissions handlers
            default:
                return true;
        }
    }

    abstract public function getDealerId(): int;
}
