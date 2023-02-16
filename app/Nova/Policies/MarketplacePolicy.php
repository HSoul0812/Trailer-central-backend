<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Marketing\Facebook\Marketplace;

/**
 * Class Marketplace
 * @package App\Nova\Policies
 */
class MarketplacePolicy
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
     * Determine whether the user can view any change integrations.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can view the change integration.
     *
     * @param NovaUser|null $user
     * @param Marketplace $entity
     * @return bool
     */
    public function view(?NovaUser $user, Marketplace $entity): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can create change integration.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can update the change integration.
     *
     * @param NovaUser $user
     * @param Marketplace $entity
     * @return bool
     */
    public function update(NovaUser $user, Marketplace $entity): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can delete the change integration.
     *
     * @param NovaUser $user
     * @param Marketplace $entity
     * @return bool
     */
    public function delete(NovaUser $user, Marketplace $entity): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the change integration.
     *
     * @param NovaUser $user
     * @param Marketplace $entity
     * @return void
     */
    public function restore(NovaUser $user, Marketplace $entity): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the change integration.
     *
     * @param NovaUser $user
     * @param Marketplace $entity
     * @return void
     */
    public function forceDelete(NovaUser $user, Marketplace $entity): void
    {
        //
    }
}

