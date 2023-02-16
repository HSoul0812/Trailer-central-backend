<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use App\Models\Parts\Type as PartType;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class PartTypePolicy
 * @package App\Nova\Policies
 */
class PartTypePolicy
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
     * Determine whether the user can view any types.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can view the type.
     *
     * @param NovaUser|null $user
     * @param PartType $type
     * @return bool
     */
    public function view(?NovaUser $user, PartType $type): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can create types.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can update the type.
     *
     * @param NovaUser $user
     * @param PartType $type
     * @return bool
     */
    public function update(NovaUser $user, PartType $type): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can delete the type.
     *
     * @param NovaUser $user
     * @param PartType $type
     * @return bool
     */
    public function delete(NovaUser $user, PartType $type): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the type.
     *
     * @param NovaUser $user
     * @param PartType $type
     * @return void
     */
    public function restore(NovaUser $user, PartType $type): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the type.
     *
     * @param NovaUser $user
     * @param PartType $type
     * @return void
     */
    public function forceDelete(NovaUser $user, PartType $type): void
    {
        //
    }
}

