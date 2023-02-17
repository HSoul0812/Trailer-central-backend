<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Integration\Collector\CollectorSpecificationRule;

/**
 * Class CollectorSpecificationRule
 * @package App\Nova\Policies
 */
class CollectorSpecificationRulePolicy
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
     * Determine whether the user can view any change specifications rules.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the change specification rules.
     *
     * @param NovaUser|null $user
     * @param CollectorSpecificationRule $CollectorSpecificationRule
     * @return bool
     */
    public function view(?NovaUser $user, CollectorSpecificationRule $CollectorSpecificationRule): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create change specification rules.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the change specification rules.
     *
     * @param NovaUser $user
     * @param CollectorSpecificationRule $CollectorSpecificationRule
     * @return bool
     */
    public function update(NovaUser $user, CollectorSpecificationRule $CollectorSpecificationRule): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the change specification rules.
     *
     * @param NovaUser $user
     * @param CollectorSpecificationRule $CollectorSpecificationRule
     * @return bool
     */
    public function delete(NovaUser $user, CollectorSpecificationRule $CollectorSpecificationRule): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the change specification rules.
     *
     * @param NovaUser $user
     * @param CollectorSpecificationRule $CollectorSpecificationRule
     * @return void
     */
    public function restore(NovaUser $user, CollectorSpecificationRule $CollectorSpecificationRule): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the change specification rules.
     *
     * @param NovaUser $user
     * @param CollectorSpecificationRule $CollectorSpecificationRule
     * @return void
     */
    public function forceDelete(NovaUser $user, CollectorSpecificationRule $CollectorSpecificationRule): void
    {
        //
    }
}

