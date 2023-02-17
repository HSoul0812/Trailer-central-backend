<?php

namespace App\Nova\Policies;

use App\Models\Marketing\Craigslist\Balance;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class BalancePolicy
 * @package App\Nova\Polices
 */
class BalancePolicy
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
     * Determine whether the user can view any balances.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the balance.
     *
     * @param NovaUser|null $user
     * @param Balance $balance
     * @return bool
     */
    public function view(?NovaUser $user, Balance $balance): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create balances.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the balance.
     *
     * @param NovaUser $user
     * @param Balance $balance
     * @return bool
     */
    public function update(NovaUser $user, Balance $balance): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the balance.
     *
     * @param NovaUser $user
     * @param Balance $balance
     * @return bool
     */
    public function delete(NovaUser $user, Balance $balance): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the balance.
     *
     * @param NovaUser $user
     * @param Balance $balance
     * @return void
     */
    public function restore(NovaUser $user, Balance $balance): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the balance.
     *
     * @param NovaUser $user
     * @param Balance $balance
     * @return void
     */
    public function forceDelete(NovaUser $user, Balance $balance): void {
        //
    }
}
