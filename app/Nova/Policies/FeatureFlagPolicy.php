<?php

namespace App\Nova\Policies;

use App\Models\FeatureFlag;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class FeatureFlagPolicy
 * @package App\Nova\Polices
 */
class FeatureFlagPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Determine whether the user can view any FeatureFlags.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool {
        return true;
    }

    /**
     * Determine whether the user can view the FeatureFlag.
     *
     * @param NovaUser|null $user
     * @param FeatureFlag $flag
     * @return bool
     */
    public function view(?NovaUser $user, FeatureFlag $flag): bool {
        return true;
    }

    /**
     * Determine whether the user can create FeatureFlags.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool {
        return false;
    }

    /**
     * Determine whether the user can update the FeatureFlag.
     *
     * @param NovaUser $user
     * @param FeatureFlag $flag
     * @return bool
     */
    public function update(NovaUser $user, FeatureFlag $flag): bool {
        return false;
    }

    /**
     * Determine whether the user can delete the FeatureFlag.
     *
     * @param NovaUser $user
     * @param FeatureFlag $flag
     * @return bool
     */
    public function delete(NovaUser $user, FeatureFlag $flag): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the FeatureFlag.
     *
     * @param NovaUser $user
     * @param FeatureFlag $flag
     * @return void
     */
    public function restore(NovaUser $user, FeatureFlag $flag): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the FeatureFlag.
     *
     * @param NovaUser $user
     * @param FeatureFlag $flag
     * @return void
     */
    public function forceDelete(NovaUser $user, FeatureFlag $flag): void {
        //
    }
}
