<?php

namespace App\Nova\Policies;

use App\Models\Feed\Factory\ShowroomGenericMap;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class ShowroomGenericMapPolicy
 * @package App\Nova\Policies
 */
class ShowroomGenericMapPolicy
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
     * Determine whether the user can view any map.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the map.
     *
     * @param NovaUser|null $user
     * @param ShowroomGenericMap $map
     * @return bool
     */
    public function view(?NovaUser $user, ShowroomGenericMap $map): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create map.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the map.
     *
     * @param NovaUser $user
     * @param ShowroomGenericMap $map
     * @return bool
     */
    public function update(NovaUser $user, ShowroomGenericMap $map): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the map.
     *
     * @param NovaUser $user
     * @param ShowroomGenericMap $map
     * @return bool
     */
    public function delete(NovaUser $user, ShowroomGenericMap $map): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the map.
     *
     * @param NovaUser $user
     * @param ShowroomGenericMap $map
     * @return void
     */
    public function restore(NovaUser $user, ShowroomGenericMap $map): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the map.
     *
     * @param NovaUser $user
     * @param ShowroomGenericMap $map
     * @return void
     */
    public function forceDelete(NovaUser $user, ShowroomGenericMap $map): void {
        //
    }
}

