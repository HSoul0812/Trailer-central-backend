<?php

namespace App\Nova\Policies;

use App\Models\Integration\Collector\Collector;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class CollectorPolicy
 * @package App\Nova\Policies
 */
class CollectorPolicy
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
     * Determine whether the user can view any collectors.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the collector.
     *
     * @param NovaUser|null $user
     * @param Collector $collector
     * @return bool
     */
    public function view(?NovaUser $user, Collector $collector): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create collectors.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the collector.
     *
     * @param NovaUser $user
     * @param Collector $collector
     * @return bool
     */
    public function update(NovaUser $user, Collector $collector): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the collector.
     *
     * @param NovaUser $user
     * @param Collector $collector
     * @return bool
     */
    public function delete(NovaUser $user, Collector $collector): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the collector.
     *
     * @param NovaUser $user
     * @param Collector $collector
     * @return void
     */
    public function restore(NovaUser $user, Collector $collector): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the collector.
     *
     * @param NovaUser $user
     * @param Collector $collector
     * @return void
     */
    public function forceDelete(NovaUser $user, Collector $collector): void {
        //
    }
}

