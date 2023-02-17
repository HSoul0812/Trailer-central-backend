<?php

namespace App\Nova\Policies;

use App\Models\CRM\Dealer\DealerFBMOverview;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class DealerFBPolicy
 * @package App\Nova\Policies
 */
class DealerFBPolicy
{
    use HandlesAuthorization;

    private const VALID_ROLES = ['Admin', 'Support', 'Sales'];

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Determine whether the user can view any fb account.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the fb account.
     *
     * @param NovaUser|null $user
     * @param DealerFBMOverview $account
     * @return bool
     */
    public function view(?NovaUser $user, DealerFBMOverview $account): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create fb accounts.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the fb account.
     *
     * @param NovaUser $user
     * @param DealerFBMOverview $account
     * @return bool
     */
    public function update(NovaUser $user, DealerFBMOverview $account): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the fb account.
     *
     * @param NovaUser $user
     * @param DealerFBMOverview $account
     * @return bool
     */
    public function delete(NovaUser $user, DealerFBMOverview $account): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the fb account.
     *
     * @param NovaUser $user
     * @param DealerFBMOverview $account
     * @return void
     */
    public function restore(NovaUser $user, DealerFBMOverview $account): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the fb account.
     *
     * @param NovaUser $user
     * @param DealerFBMOverview $account
     * @return void
     */
    public function forceDelete(NovaUser $user, DealerFBMOverview $account): void {
        //
    }
}
