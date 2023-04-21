<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\User\Integration\DealerIntegration;

/**
 * Class DealerIntegrationPolicy
 * @package App\Nova\Policies
 */
class DealerIntegrationPolicy
{
    use HandlesAuthorization;

    private const VALID_ROLES = ['Admin', 'Support'];

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
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the brand.
     *
     * @param NovaUser|null $user
     * @param DealerIntegration $dealerIntegration
     * @return bool
     */
    public function view(?NovaUser $user, DealerIntegration $dealerIntegration): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create brands.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the brand.
     *
     * @param NovaUser $user
     * @param DealerIntegration $dealerIntegration
     * @return bool
     */
    public function update(NovaUser $user, DealerIntegration $dealerIntegration): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the brand.
     *
     * @param NovaUser $user
     * @param DealerIntegration $dealerIntegration
     * @return bool
     */
    public function delete(NovaUser $user, DealerIntegration $dealerIntegration): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the brand.
     *
     * @param NovaUser $user
     * @param DealerIntegration $dealerIntegration
     * @return void
     */
    public function restore(NovaUser $user, DealerIntegration $dealerIntegration): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the brand.
     *
     * @param NovaUser $user
     * @param DealerIntegration $dealerIntegration
     * @return void
     */
    public function forceDelete(NovaUser $user, DealerIntegration $dealerIntegration): void
    {
        //
    }
}
