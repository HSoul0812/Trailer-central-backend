<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use App\Models\Inventory\Inventory;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class InventoryPolicy
 * @package App\Nova\Policies
 */
class InventoryPolicy
{
    use HandlesAuthorization;

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
     * Determine whether the user can view any inventory.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can view the inventory.
     *
     * @param NovaUser|null $user
     * @param Inventory $inventory
     * @return bool
     */
    public function view(?NovaUser $user, Inventory $inventory): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can create inventories.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can update the inventory.
     *
     * @param NovaUser $user
     * @param Inventory $inventory
     * @return bool
     */
    public function update(NovaUser $user, Inventory $inventory): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can delete the inventory.
     *
     * @param NovaUser $user
     * @param Inventory $inventory
     * @return bool
     */
    public function delete(NovaUser $user, Inventory $inventory): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the inventory.
     *
     * @param NovaUser $user
     * @param Inventory $inventory
     * @return void
     */
    public function restore(NovaUser $user, Inventory $inventory): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the inventory.
     *
     * @param NovaUser $user
     * @param Inventory $inventory
     * @return void
     */
    public function forceDelete(NovaUser $user, Inventory $inventory): void
    {
        //
    }
}

