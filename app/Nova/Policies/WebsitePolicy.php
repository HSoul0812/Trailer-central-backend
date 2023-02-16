<?php

namespace App\Nova\Policies;

use App\Models\Integration\Collector\Collector;
use App\Models\User\NovaUser;
use App\Models\Website\Website;
use Spatie\Permission\Models\Permission;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class WebsitePolicy
 * @package App\Nova\Policies
 */
class WebsitePolicy
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
     * Determine whether the user can view any website.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support', 'Sales');
    }

    /**
     * Determine whether the user can view the website.
     *
     * @param NovaUser|null $user
     * @param Website $website
     * @return bool
     */
    public function view(?NovaUser $user, Website $website): bool
    {
        return $user->hasAnyRole('Admin', 'Support', 'Sales');
    }

    /**
     * Determine whether the user can create website.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support', 'Sales');
    }

    /**
     * Determine whether the user can update the website.
     *
     * @param NovaUser $user
     * @param Website $website
     * @return bool
     */
    public function update(NovaUser $user, Website $website): bool
    {
        return $user->hasAnyRole('Admin', 'Support', 'Sales');
    }

    /**
     * Determine whether the user can delete the website.
     *
     * @param NovaUser $user
     * @param Website $website
     * @return bool
     */
    public function delete(NovaUser $user, Website $website): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the website.
     *
     * @param NovaUser $user
     * @param Website $website
     * @return void
     */
    public function restore(NovaUser $user, Website $website): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the website.
     *
     * @param NovaUser $user
     * @param Website $website
     * @return void
     */
    public function forceDelete(NovaUser $user, Website $website): void
    {
        //
    }
}

