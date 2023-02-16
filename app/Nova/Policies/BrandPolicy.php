<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Spatie\Permission\Models\Permission;
use App\Models\Inventory\Manufacturers\Brand;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class BrandPolicy
 * @package App\Nova\Policies
 */
class BrandPolicy
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
     * @param Brand $brand
     * @return bool
     */
    public function view(?NovaUser $user, Brand $brand): bool
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
     * @param Brand $brand
     * @return bool
     */
    public function update(NovaUser $user, Brand $brand): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can delete the brand.
     *
     * @param NovaUser $user
     * @param Brand $brand
     * @return bool
     */
    public function delete(NovaUser $user, Brand $brand): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the brand.
     *
     * @param NovaUser $user
     * @param Brand $brand
     * @return void
     */
    public function restore(NovaUser $user, Brand $brand): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the brand.
     *
     * @param NovaUser $user
     * @param Brand $brand
     * @return void
     */
    public function forceDelete(NovaUser $user, Brand $brand): void
    {
        //
    }
}

