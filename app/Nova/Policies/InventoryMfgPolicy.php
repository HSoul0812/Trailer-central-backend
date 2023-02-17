<?php

namespace App\Nova\Policies;

use App\Models\Inventory\InventoryMfg;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class InventoryMfgPolicy
 * @package App\Nova\Polices
 */
class InventoryMfgPolicy
{
    use HandlesAuthorization;

    private const VALID_ROLES = ['Admin', 'Support', 'DataSupport'];

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
     * @param InventoryMfg $manufacturer
     * @return bool
     */
    public function view(?NovaUser $user, InventoryMfg $manufacturer): bool {
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
     * @param InventoryMfg $manufacturer
     * @return bool
     */
    public function update(NovaUser $user, InventoryMfg $manufacturer): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the balance.
     *
     * @param NovaUser $user
     * @param InventoryMfg $manufacturer
     * @return bool
     */
    public function delete(NovaUser $user, InventoryMfg $manufacturer): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the balance.
     *
     * @param NovaUser $user
     * @param InventoryMfg $manufacturer
     * @return void
     */
    public function restore(NovaUser $user, InventoryMfg $manufacturer): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the balance.
     *
     * @param NovaUser $user
     * @param InventoryMfg $manufacturer
     * @return void
     */
    public function forceDelete(NovaUser $user, InventoryMfg $manufacturer): void {
        //
    }
}
