<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;


/**
 * Class UserPolicy
 * @package App\Nova\Policies
 */
class UserPolicy
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
        return $user->hasAnyRole('Admin');
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param NovaUser|null $user
     * @param NovaUser $novaUser
     * @return bool
     */
    public function view(?NovaUser $user, NovaUser $novaUser): bool
    {
        return $user->hasAnyRole('Admin');
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin');
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param NovaUser $user
     * @param NovaUser $novaUser
     * @return bool
     */
    public function update(NovaUser $user, NovaUser $novaUser): bool
    {
        return $user->hasAnyRole('Admin');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param NovaUser $user
     * @param NovaUser $novaUser
     * @return bool
     */
    public function delete(NovaUser $user, NovaUser $novaUser): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param NovaUser $user
     * @param NovaUser $novaUser
     * @return void
     */
    public function restore(NovaUser $user, NovaUser $novaUser)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the user.
     *
     * @param NovaUser $user
     * @param NovaUser $novaUser
     * @return void
     */
    public function forceDelete(NovaUser $user, NovaUser $novaUser)
    {
        //
    }
}
