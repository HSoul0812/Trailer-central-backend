<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use App\Models\Parts\Brand as PartBrand;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class PartBrand
 * @package App\Nova\Policies
 */
class PartBrandPolicy
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
     * Determine whether the user can view any brand.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can view the brand.
     *
     * @param NovaUser|null $user
     * @param PartBrand $brand
     * @return bool
     */
    public function view(?NovaUser $user, PartBrand $brand): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can create brands.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can update the brand.
     *
     * @param NovaUser $user
     * @param PartBrand $brand
     * @return bool
     */
    public function update(NovaUser $user, PartBrand $brand): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can delete the brand.
     *
     * @param NovaUser $user
     * @param PartBrand $brand
     * @return bool
     */
    public function delete(NovaUser $user, PartBrand $brand): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the brand.
     *
     * @param NovaUser $user
     * @param PartBrand $brand
     * @return void
     */
    public function restore(NovaUser $user, PartBrand $brand): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the brand.
     *
     * @param NovaUser $user
     * @param PartBrand $brand
     * @return void
     */
    public function forceDelete(NovaUser $user, PartBrand $brand): void
    {
        //
    }
}

