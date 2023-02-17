<?php

namespace App\Nova\Policies;

use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class QuickbookApprovalPolicy
 * @package App\Nova\Policies
 */
class QuickbookApprovalPolicy
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
     * Determine whether the user can view any approval.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the approval.
     *
     * @param NovaUser|null $user
     * @param QuickbookApproval $approval
     * @return bool
     */
    public function view(?NovaUser $user, QuickbookApproval $approval): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create approvals.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the approval.
     *
     * @param NovaUser $user
     * @param QuickbookApproval $approval
     * @return bool
     */
    public function update(NovaUser $user, QuickbookApproval $approval): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the approval.
     *
     * @param NovaUser $user
     * @param QuickbookApproval $approval
     * @return bool
     */
    public function delete(NovaUser $user, QuickbookApproval $approval): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the approval.
     *
     * @param NovaUser $user
     * @param QuickbookApproval $approval
     * @return void
     */
    public function restore(NovaUser $user, QuickbookApproval $approval): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the approval.
     *
     * @param NovaUser $user
     * @param QuickbookApproval $approval
     * @return void
     */
    public function forceDelete(NovaUser $user, QuickbookApproval $approval): void {
        //
    }
}

