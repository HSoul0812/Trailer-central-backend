<?php

namespace App\Nova\Policies;

use App\Models\Inventory\Category;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class CategoryPolicy
 * @package App\Nova\Policies
 */
class CategoryPolicy
{
    use HandlesAuthorization;

    private const VALID_ROLES = ['Admin', 'Support'];

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Determine whether the user can view any category.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the category.
     *
     * @param NovaUser|null $user
     * @param Category $category
     * @return bool
     */
    public function view(?NovaUser $user, Category $category): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create categories.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the category.
     *
     * @param NovaUser $user
     * @param Category $category
     * @return bool
     */
    public function update(NovaUser $user, Category $category): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the category.
     *
     * @param NovaUser $user
     * @param Category $category
     * @return bool
     */
    public function delete(NovaUser $user, Category $category): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the category.
     *
     * @param NovaUser $user
     * @param Category $category
     * @return void
     */
    public function restore(NovaUser $user, Category $category): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the category.
     *
     * @param NovaUser $user
     * @param Category $category
     * @return void
     */
    public function forceDelete(NovaUser $user, Category $category): void {
        //
    }
}

