<?php

namespace App\Nova\Policies;

use App\Models\Feed\TransactionExecuteQueue;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class TransactionExecuteQueuePolicy
 * @package App\Nova\Policies
 */
class TransactionExecuteQueuePolicy
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
     * Determine whether the user can view any transaction.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the transaction.
     *
     * @param NovaUser|null $user
     * @param TransactionExecuteQueue $transaction
     * @return bool
     */
    public function view(?NovaUser $user, TransactionExecuteQueue $transaction): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create transaction.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool {
        return false;
    }

    /**
     * Determine whether the user can update the transaction.
     *
     * @param NovaUser $user
     * @param TransactionExecuteQueue $transaction
     * @return bool
     */
    public function update(NovaUser $user, TransactionExecuteQueue $transaction): bool {
        return false;
    }

    /**
     * Determine whether the user can delete the transaction.
     *
     * @param NovaUser $user
     * @param TransactionExecuteQueue $transaction
     * @return bool
     */
    public function delete(NovaUser $user, TransactionExecuteQueue $transaction): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the transaction.
     *
     * @param NovaUser $user
     * @param TransactionExecuteQueue $transaction
     * @return void
     */
    public function restore(NovaUser $user, TransactionExecuteQueue $transaction): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the transaction.
     *
     * @param NovaUser $user
     * @param TransactionExecuteQueue $transaction
     * @return void
     */
    public function forceDelete(NovaUser $user, TransactionExecuteQueue $transaction): void {
        //
    }
}

