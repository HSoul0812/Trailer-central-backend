<?php

namespace App\Traits\Models;

use App\Models\User\DealerClapp;
use App\Models\User\NewDealerUser;
use App\Models\User\Interfaces\PermissionsInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
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
     * @var string
     */
    protected $permissionLevelKey = 'permission_level';

    /**
     * @var string
     */
    protected $permissionFeatureKey = 'feature';

    /**
     * @return Collection
     */
    public function getPermissions(): Collection {
        $perms = [];

        if ($this->userPermissions === null) {
            $permissions = $this->perms()->get();

            // Override Perms?
            foreach ($permissions as $perm) {
                if ($this->hasNoPermission($perm->feature, $perm->permission_level)) {
                    $perm->permission_level = PermissionsInterface::CANNOT_SEE_PERMISSION;
                }

                $perms[] = $perm;
            }

            // Return Use Permissions Collection
            $this->userPermissions = collect($perms);
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
        // Get Default Perms
        $perms = [];
        $permissions = $this->perms()
            ->where(function($query) {
                $query->where($this->permissionLevelKey, '!=', PermissionsInterface::CANNOT_SEE_PERMISSION);
            })->get();

        // Override Perms?
        foreach ($permissions as $perm) {
            if ($this->hasNoPermission($perm->feature, $perm->permission_level)) {
                $perm->permission_level = PermissionsInterface::CANNOT_SEE_PERMISSION;
            }

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
        $currentPermission = $this->getPermissions()->first(function ($permission, $key) use ($feature, $permissionLevel) {
            return strcmp($permission[$this->permissionFeatureKey], $feature) === 0 && strcmp($permission[$this->permissionLevelKey], $permissionLevel) === 0;
        });

        return !empty($currentPermission);
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
    public function hasNoPermission(string $feature, string $permissionLevel): bool
    {
        switch ($feature) {
            case 'marketing':
                return !$this->hasMarketingPermission();
            // more permissions handlers
            default:
                return false;
        }
    }

    /**
     * @param string $feature
     * @return bool
     */
    public function hasPermissionCanSeeAndChange(string $feature): bool
    {
        return $this->hasPermission($feature, PermissionsInterface::CAN_SEE_AND_CHANGE_PERMISSION);
    }

    /**
     * The method must return Relation class or Builder class object, for getting DealerUserPermission Collection.
     *
     * @return Relation|Builder
     */
    abstract public function perms();

    abstract public function getDealerId(): int;
}
