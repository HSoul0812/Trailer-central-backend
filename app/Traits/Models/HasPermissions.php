<?php

namespace App\Traits\Models;

use Illuminate\Support\Collection;

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

    abstract public function perms();
}
