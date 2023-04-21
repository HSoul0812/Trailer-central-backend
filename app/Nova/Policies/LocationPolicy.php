<?php

namespace App\Nova\Policies;

use App\Models\User\DealerLocation;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;


/**
 * Class LocationPolicy
 * @package App\Nova\Policies
 */
class LocationPolicy
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
     * Determine whether the user can view any locations.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the location.
     *
     * @param NovaUser|null $user
     * @param DealerLocation $location
     * @return bool
     */
    public function view(?NovaUser $user, DealerLocation $location): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create locations.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool {
        return false;
    }

    /**
     * Determine whether the user can update the location.
     *
     * @param NovaUser $user
     * @param DealerLocation $location
     * @return bool
     */
    public function update(NovaUser $user, DealerLocation $location): bool {
        return false;
    }

    /**
     * Determine whether the user can delete the location.
     *
     * @param NovaUser $user
     * @param DealerLocation $location
     * @return bool
     */
    public function delete(NovaUser $user, DealerLocation $location): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the location.
     *
     * @param NovaUser $user
     * @param DealerLocation $location
     * @return void
     */
    public function restore(NovaUser $user, DealerLocation $location): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the location.
     *
     * @param NovaUser $user
     * @param DealerLocation $location
     * @return void
     */
    public function forceDelete(NovaUser $user, DealerLocation $location): void {
        //
    }
}
