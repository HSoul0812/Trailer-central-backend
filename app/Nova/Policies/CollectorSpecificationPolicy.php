<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Integration\Collector\CollectorSpecification;

/**
 * Class CollectorSpecification
 * @package App\Nova\Policies
 */
class CollectorSpecificationPolicy
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
     * Determine whether the user can view any change specifications.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can view the change specifications.
     *
     * @param NovaUser|null $user
     * @param CollectorSpecification $CollectorSpecification
     * @return bool
     */
    public function view(?NovaUser $user, CollectorSpecification $CollectorSpecification): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can create change specificationss.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can update the change specifications.
     *
     * @param NovaUser $user
     * @param CollectorSpecification $CollectorSpecification
     * @return bool
     */
    public function update(NovaUser $user, CollectorSpecification $CollectorSpecification): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can delete the change specifications.
     *
     * @param NovaUser $user
     * @param CollectorSpecification $CollectorSpecification
     * @return bool
     */
    public function delete(NovaUser $user, CollectorSpecification $CollectorSpecification): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the change specifications.
     *
     * @param NovaUser $user
     * @param CollectorSpecification $CollectorSpecification
     * @return void
     */
    public function restore(NovaUser $user, CollectorSpecification $CollectorSpecification): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the change specifications.
     *
     * @param NovaUser $user
     * @param CollectorSpecification $CollectorSpecification
     * @return void
     */
    public function forceDelete(NovaUser $user, CollectorSpecification $CollectorSpecification): void
    {
        //
    }
}

