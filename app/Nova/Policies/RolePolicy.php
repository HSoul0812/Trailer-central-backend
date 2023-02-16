<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class RolePolicy
 * @package App\Nova\Polices
 */
class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can view any roles.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin');
    }

    /**
     * Determine whether the user can view the role.
     *
     * @param  NovaUser|null $user
     * @param  Role $role
     * @return bool
     */
    public function view(?NovaUser $user, Role $role): bool
    {
        return $user->hasAnyRole('Admin');
    }

    /**
     * Determine whether the user can create roles.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin');
    }

    /**
     * Determine whether the user can update the role.
     *
     * @param  NovaUser $user
     * @param  Role $role
     * @return bool
     */
    public function update(NovaUser $user, Role $role): bool
    {
        return $user->hasAnyRole('Admin');
    }

    /**
     * Determine whether the user can delete the role.
     *
     * @param  NovaUser $user
     * @param  Role $role
     * @return bool
     */
    public function delete(NovaUser $user, Role $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the role.
     *
     * @param  NovaUser $user
     * @param  Role $role
     * @return void
     */
    public function restore(NovaUser $user, Role $role): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the role.
     *
     * @param  NovaUser $user
     * @param  Role $role
     * @return void
     */
    public function forceDelete(NovaUser $user, Role $role): void
    {
        //
    }
}
