<?php

namespace App\Nova\Policies;

use App\Models\Parts\Vendor;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class PartTypePolicy
 * @package App\Nova\Policies
 */
class PartVendorPolicy
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
     * Determine whether the user can view any vendor.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can view the vendor.
     *
     * @param NovaUser|null $user
     * @param Vendor $vendor
     * @return bool
     */
    public function view(?NovaUser $user, Vendor $vendor): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can create vendors.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can update the vendor.
     *
     * @param NovaUser $user
     * @param Vendor $vendor
     * @return bool
     */
    public function update(NovaUser $user, Vendor $vendor): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can delete the vendor.
     *
     * @param NovaUser $user
     * @param Vendor $vendor
     * @return bool
     */
    public function delete(NovaUser $user, Vendor $vendor): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the vendor.
     *
     * @param NovaUser $user
     * @param Vendor $vendor
     * @return void
     */
    public function restore(NovaUser $user, Vendor $vendor): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the vendor.
     *
     * @param NovaUser $user
     * @param Vendor $vendor
     * @return void
     */
    public function forceDelete(NovaUser $user, Vendor $vendor): void
    {
        //
    }
}

