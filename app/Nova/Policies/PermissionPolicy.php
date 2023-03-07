<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Permission;

/**
 * Class PermissionPolicy
 * @package App\Nova\Policies
 */
class PermissionPolicy
{
    use HandlesAuthorization;

    private const VALID_ROLES = ['Admin'];

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Determine whether the user can view any permissions.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the permission.
     *
     * @param NovaUser|null $user
     * @param Permission $permission
     * @return bool
     */
    public function view(?NovaUser $user, Permission $permission): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create permissions.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the permission.
     *
     * @param NovaUser $user
     * @param Permission $permission
     * @return bool
     */
    public function update(NovaUser $user, Permission $permission): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the permission.
     *
     * @param NovaUser $user
     * @param Permission $permission
     * @return bool
     */
    public function delete(NovaUser $user, Permission $permission): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the permission.
     *
     * @param NovaUser $user
     * @param Permission $permission
     * @return void
     */
    public function restore(NovaUser $user, Permission $permission): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the permission.
     *
     * @param NovaUser $user
     * @param Permission $permission
     * @return void
     */
    public function forceDelete(NovaUser $user, Permission $permission): void {
        //
    }
}

