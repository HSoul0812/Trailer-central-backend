<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;

/**
 * Class PolicyManager
 * @package App\Nova\Policies
 */
class PolicyManager
{
    private $validRoles;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct(array $validRoles)
    {
        $this->validRoles = $validRoles;
    }

    /**
     * Determine whether the user can view any record.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole($this->validRoles);
    }

    /**
     * Determine whether the user can view the record.
     *
     * @param NovaUser|null $user
     * @param $model
     * @return bool
     */
    public function view(?NovaUser $user, $model): bool
    {
        return $user->hasAnyRole($this->validRoles);
    }

    /**
     * Determine whether the user can create records.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole($this->validRoles);
    }

    /**
     * Determine whether the user can update the record.
     *
     * @param NovaUser $user
     * @param $model
     * @return bool
     */
    public function update(NovaUser $user, $model): bool
    {
        return $user->hasAnyRole($this->validRoles);
    }

    /**
     * Determine whether the user can delete the record.
     *
     * @param NovaUser $user
     * @param $model
     * @return bool
     */
    public function delete(NovaUser $user, $model): bool
    {
        return config('app.env') !== 'production';
    }

    /**
     * Determine whether the user can restore the record.
     *
     * @param NovaUser $user
     * @param $model
     * @return void
     */
    public function restore(NovaUser $user, $model): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the record.
     *
     * @param NovaUser $user
     * @param $model
     * @return void
     */
    public function forceDelete(NovaUser $user, $model): void
    {
        //
    }
}
