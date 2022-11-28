<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Integration\Collector\CollectorChangeReport;

/**
 * Class CollectorChangeReportPolicy
 * @package App\Nova\Policies
 */
class CollectorChangeReportPolicy
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
     * @param CollectorChangeReport $report
     * @return bool
     */
    public function view(?NovaUser $user, CollectorChangeReport $report): bool
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
     * @param CollectorChangeReport $report
     * @return bool
     */
    public function update(NovaUser $user, CollectorChangeReport $report): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the change report.
     *
     * @param NovaUser $user
     * @param CollectorChangeReport $report
     * @return bool
     */
    public function delete(NovaUser $user, CollectorChangeReport $report): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the change report.
     *
     * @param NovaUser $user
     * @param CollectorChangeReport $report
     * @return void
     */
    public function restore(NovaUser $user, CollectorChangeReport $report): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the change report.
     *
     * @param NovaUser $user
     * @param CollectorChangeReport $report
     * @return void
     */
    public function forceDelete(NovaUser $user, CollectorChangeReport $report): void
    {
        //
    }
}

