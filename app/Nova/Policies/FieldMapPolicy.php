<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use App\Models\Website\Forms\FieldMap;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class FieldMapPolicy
 * @package App\Nova\Policies
 */
class FieldMapPolicy
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
     * Determine whether the user can view any field map.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can view the field map.
     *
     * @param NovaUser|null $user
     * @param FieldMap $field
     * @return bool
     */
    public function view(?NovaUser $user, FieldMap $field): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can create field maps.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can update the field map.
     *
     * @param NovaUser $user
     * @param FieldMap $field
     * @return bool
     */
    public function update(NovaUser $user, FieldMap $field): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can delete the field map.
     *
     * @param NovaUser $user
     * @param FieldMap $field
     * @return bool
     */
    public function delete(NovaUser $user, FieldMap $field): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the field map.
     *
     * @param NovaUser $user
     * @param FieldMap $field
     * @return void
     */
    public function restore(NovaUser $user, FieldMap $field): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the field map.
     *
     * @param NovaUser $user
     * @param FieldMap $field
     * @return void
     */
    public function forceDelete(NovaUser $user, FieldMap $field): void
    {
        //
    }
}

