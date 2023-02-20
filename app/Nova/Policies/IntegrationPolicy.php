<?php
namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use App\Models\Integration\Integration;

use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class Integration
 * @package App\Nova\Policies
 */
class IntegrationPolicy
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
     * Determine whether the user can view any change integrations.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }
    /**
     * Determine whether the user can view the change integration.
     *
     * @param NovaUser|null $user
     * @param Integration $integration
     * @return bool
     */
    public function view(?NovaUser $user, Integration $integration): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }
    /**
     * Determine whether the user can create change integration.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }
    /**
     * Determine whether the user can update the change integration.
     *
     * @param NovaUser $user
     * @param Integration $integration
     * @return bool
     */
    public function update(NovaUser $user, Integration $integration): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }
    /**
     * Determine whether the user can delete the change integration.
     *
     * @param NovaUser $user
     * @param Integration $integration
     * @return bool
     */
    public function delete(NovaUser $user, Integration $integration): bool
    {
        return false;
    }
    /**
     * Determine whether the user can restore the change integrations.
     *
     * @param NovaUser $user
     * @param Integration $integration
     * @return void
     */
    public function restore(NovaUser $user, Integration $integration): void
    {
        //
    }
    /**
     * Determine whether the user can permanently delete the change integrations.
     *
     * @param NovaUser $user
     * @param Integration $integration
     * @return void
     */
    public function forceDelete(NovaUser $user, Integration $integration): void
    {
        //
    }
}
