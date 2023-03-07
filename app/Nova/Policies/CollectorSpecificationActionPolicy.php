<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Integration\Collector\CollectorSpecificationAction;

/**
 * Class CollectorSpecificationAction
 * @package App\Nova\Policies
 */
class CollectorSpecificationActionPolicy
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
     * Determine whether the user can view any change specification actions.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the change specification actions.
     *
     * @param NovaUser|null $user
     * @param CollectorSpecificationAction $CollectorSpecificationAction
     * @return bool
     */
    public function view(?NovaUser $user, CollectorSpecificationAction $CollectorSpecificationAction): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create change specification actions.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the change specification actions.
     *
     * @param NovaUser $user
     * @param CollectorSpecificationAction $CollectorSpecificationAction
     * @return bool
     */
    public function update(NovaUser $user, CollectorSpecificationAction $CollectorSpecificationAction): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the change specification actions.
     *
     * @param NovaUser $user
     * @param CollectorSpecificationAction $CollectorSpecificationAction
     * @return bool
     */
    public function delete(NovaUser $user, CollectorSpecificationAction $CollectorSpecificationAction): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the change specification actions.
     *
     * @param NovaUser $user
     * @param CollectorSpecificationAction $CollectorSpecificationAction
     * @return void
     */
    public function restore(NovaUser $user, CollectorSpecificationAction $CollectorSpecificationAction): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the change specification actions.
     *
     * @param NovaUser $user
     * @param CollectorSpecificationAction $CollectorSpecificationAction
     * @return void
     */
    public function forceDelete(NovaUser $user, CollectorSpecificationAction $CollectorSpecificationAction): void
    {
        //
    }
}

