<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use App\Models\CRM\Leads\Lead;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class LeadPolicy
 * @package App\Nova\Policies
 */
class LeadPolicy
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
     * Determine whether the user can view any lead.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can view the lead.
     *
     * @param NovaUser|null $user
     * @param Lead $lead
     * @return bool
     */
    public function view(?NovaUser $user, Lead $lead): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can create leads.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can update the lead.
     *
     * @param NovaUser $user
     * @param Lead $lead
     * @return bool
     */
    public function update(NovaUser $user, Lead $lead): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can delete the lead.
     *
     * @param NovaUser $user
     * @param Lead $lead
     * @return bool
     */
    public function delete(NovaUser $user, Lead $lead): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can restore the lead.
     *
     * @param NovaUser $user
     * @param Lead $lead
     * @return void
     */
    public function restore(NovaUser $user, Lead $lead): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the lead.
     *
     * @param NovaUser $user
     * @param Lead $lead
     * @return void
     */
    public function forceDelete(NovaUser $user, Lead $lead): void
    {
        //
    }
}

