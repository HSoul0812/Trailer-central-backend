<?php

namespace App\Traits\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use App\Models\User\Interfaces\PermissionsInterface;

/**
 * Trait HasPermissions
 * @package App\Traits\Models
 */
trait HasPermissions
{
    /**
     * @var Collection
     */
    private $userPermissions;

    /**
     * @return Collection
     */
    public function getPermissions(): Collection
    {
        if ($this->userPermissions === null) {
            $this->userPermissions = $this->perms()->get();
        }

        return $this->userPermissions;
    }
    
    /**
     * Returns permissions allowed for a given user
     * 
     * @return Collection
     */
    public function getPermissionsAllowed(): Collection
    {
        return $this->perms()
                    ->where(function($query) {
                        $query->where('permission_level', '!=', PermissionsInterface::CANNOT_SEE_PERMISSION);
                    })->get();
    }

    /**
     * @param string $feature
     * @param string $permissionLevel
     * @return bool
     */
    public function hasPermission(string $feature, string $permissionLevel): bool
    {
        $currentPermission = $this->getPermissions()->first(function ($permission, $key) use ($feature, $permissionLevel) {
            return strcmp($permission['feature'], $feature) === 0 && strcmp($permission['permission_level'], $permissionLevel) === 0;
        });

        return !empty($currentPermission);
    }

    /**
     * The method must return Relation class or Builder class object, for getting DealerUserPermission Collection.
     *
     * @return Relation|Builder
     */
    abstract public function perms();
}
