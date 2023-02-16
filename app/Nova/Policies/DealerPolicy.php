<?php

namespace App\Nova\Policies;

use App\Models\User\User;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

use Spatie\Permission\Models\Role;

/**
 * Class DealerPolicy
 * @package App\Nova\Policies
 */
class DealerPolicy
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
     * Determine whether the user can view any user.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support', 'Sales');
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param  NovaUser|null $user
     * @param  User $dealer
     * @return bool
     */
    public function view(?NovaUser $user, User $dealer): bool
    {
        return $user->hasAnyRole('Admin', 'Support', 'Sales');
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support', 'Sales');
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  NovaUser  $user
     * @param  User $dealer
     * @return bool
     */
    public function update(NovaUser $user, User $dealer): bool
    {
        return $user->hasAnyRole('Admin', 'Support', 'Sales');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  NovaUser  $user
     * @param  User $dealer
     * @return bool
     */
    public function delete(NovaUser $user, User $dealer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  NovaUser  $user
     * @param  User $dealer
     * @return void
     */
    public function restore(NovaUser $user, User $dealer): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the user.
     *
     * @param  NovaUser  $user
     * @param  User $dealer
     * @return void
     */
    public function forceDelete(NovaUser $user, User $dealer): void
    {
        //
    }
}
