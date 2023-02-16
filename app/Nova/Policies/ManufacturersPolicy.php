<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Inventory\Manufacturers\Manufacturers;

/**
 * Class ManufacturersPolicy
 * @package App\Nova\Policies
 */
class ManufacturersPolicy
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
     * Determine whether the user can view any manufacturer.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can view the manufacturer.
     *
     * @param NovaUser|null $user
     * @param Manufacturers $manufacturer
     * @return bool
     */
    public function view(?NovaUser $user, Manufacturers $manufacturer): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can create manufacturer.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can update the manufacturer.
     *
     * @param NovaUser $user
     * @param Manufacturers $manufacturer
     * @return bool
     */
    public function update(NovaUser $user, Manufacturers $manufacturer): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can delete the manufacturer.
     *
     * @param NovaUser $user
     * @param Manufacturers $manufacturer
     * @return bool
     */
    public function delete(NovaUser $user, Manufacturers $manufacturer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the manufacturer.
     *
     * @param NovaUser $user
     * @param Manufacturers $manufacturer
     * @return void
     */
    public function restore(NovaUser $user, Manufacturers $manufacturer): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the manufacturer.
     *
     * @param NovaUser $user
     * @param Manufacturers $manufacturer
     * @return void
     */
    public function forceDelete(NovaUser $user, Manufacturers $manufacturer): void
    {
        //
    }
}

