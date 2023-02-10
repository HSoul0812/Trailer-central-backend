<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Integration\Collector\CollectorLog;

/**
 * Class CollectorLog
 * @package App\Nova\Policies
 */
class CollectorLogPolicy
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
     * Determine whether the user can view any change reports.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can view the change report.
     *
     * @param NovaUser|null $user
     * @param CollectorLog $report
     * @return bool
     */
    public function view(?NovaUser $user, CollectorLog $report): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can create change reports.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the change report.
     *
     * @param NovaUser $user
     * @param CollectorLog $report
     * @return bool
     */
    public function update(NovaUser $user, CollectorLog $report): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the change report.
     *
     * @param NovaUser $user
     * @param CollectorLog $report
     * @return bool
     */
    public function delete(NovaUser $user, CollectorLog $report): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the change report.
     *
     * @param NovaUser $user
     * @param CollectorLog $report
     * @return void
     */
    public function restore(NovaUser $user, CollectorLog $report): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the change report.
     *
     * @param NovaUser $user
     * @param CollectorLog $report
     * @return void
     */
    public function forceDelete(NovaUser $user, CollectorLog $report): void
    {
        //
    }
}

