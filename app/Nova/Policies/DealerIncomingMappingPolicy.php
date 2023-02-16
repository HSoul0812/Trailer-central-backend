<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Feed\Mapping\Incoming\DealerIncomingMapping;

/**
 * Class DealerIncomingMapping
 * @package App\Nova\Policies
 */
class DealerIncomingMappingPolicy
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
     * Determine whether the user can view any mappings.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can view the mapping.
     *
     * @param NovaUser|null $user
     * @param DealerIncomingMapping $mapping
     * @return bool
     */
    public function view(?NovaUser $user, DealerIncomingMapping $mapping): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can create mappings.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can update the mapping.
     *
     * @param NovaUser $user
     * @param DealerIncomingMapping $mapping
     * @return bool
     */
    public function update(NovaUser $user, DealerIncomingMapping $mapping): bool
    {
        return $user->hasAnyRole('Admin', 'Support');
    }

    /**
     * Determine whether the user can delete the mapping.
     *
     * @param NovaUser $user
     * @param DealerIncomingMapping $mapping
     * @return bool
     */
    public function delete(NovaUser $user, DealerIncomingMapping $mapping): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the mapping.
     *
     * @param NovaUser $user
     * @param DealerIncomingMapping $mapping
     * @return void
     */
    public function restore(NovaUser $user, DealerIncomingMapping $mapping): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the mapping.
     *
     * @param NovaUser $user
     * @param DealerIncomingMapping $mapping
     * @return void
     */
    public function forceDelete(NovaUser $user, DealerIncomingMapping $mapping): void
    {
        //
    }
}

