<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use App\Models\Website\Website;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class WebsitePolicy
 * @package App\Nova\Policies
 */
class WebsitePolicy
{
    use HandlesAuthorization;

    private const VALID_ROLES = ['Admin', 'Support', 'Sales', 'DataSupport'];

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Determine whether the user can view any website.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the website.
     *
     * @param NovaUser|null $user
     * @param Website $website
     * @return bool
     */
    public function view(?NovaUser $user, Website $website): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create website.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the website.
     *
     * @param NovaUser $user
     * @param Website $website
     * @return bool
     */
    public function update(NovaUser $user, Website $website): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the website.
     *
     * @param NovaUser $user
     * @param Website $website
     * @return bool
     */
    public function delete(NovaUser $user, Website $website): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the website.
     *
     * @param NovaUser $user
     * @param Website $website
     * @return void
     */
    public function restore(NovaUser $user, Website $website): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the website.
     *
     * @param NovaUser $user
     * @param Website $website
     * @return void
     */
    public function forceDelete(NovaUser $user, Website $website): void {
        //
    }
}

