<?php

namespace App\Traits\Models;

use Illuminate\Support\Collection;
use App\Models\User\DealerUserPermission;
use App\Models\User\Interfaces\PermissionsInterface;
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
        
        foreach($permissions as $perm) {
            $perm->permission_level = PermissionsInterface::CAN_SEE_AND_CHANGE_PERMISSION;
            $perms[] = $perm;
        }
        
        return collect($perms);
    }

    /**
     * @param string $feature
     * @param string $permissionLevel
     * @return bool
     */
    public function hasPermission(string $feature, string $permissionLevel): bool
    {
        return false;
    }
}
