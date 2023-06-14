<?php

namespace App\Nova\Policies;

use App\Models\User\DealerLocation;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;


/**
 * Class LocationPolicy
 * @package App\Nova\Policies
 */
class LocationPolicy extends PolicyManager
{
    use HandlesAuthorization;

    /**
     * @var array
     */
    private const VALID_ROLES = ['Admin', 'Support', 'Sales', 'DataSupport'];

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct(
            self::VALID_ROLES
        );
    }

    /**
     * {@inheritDoc}
     */
    public function create(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the location.
     *
     * @param NovaUser $user
     * @param DealerLocation $location
     * @return bool
     */
    public function update(NovaUser $user, DealerLocation $location): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the location.
     *
     * @param NovaUser $user
     * @param DealerLocation $location
     * @return bool
     */
    public function delete(NovaUser $user, DealerLocation $location): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
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
