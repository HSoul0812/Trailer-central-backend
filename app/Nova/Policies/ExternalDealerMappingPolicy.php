<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Feed\Mapping\ExternalDealerMapping;

/**
 * Class ExternalDealerMapping
 * @package App\Nova\Policies
 */
class ExternalDealerMappingPolicy
{
    use HandlesAuthorization;

    private const VALID_ROLES = ['Admin', 'Support'];

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
     * Determine whether the user can view any change external dealer mappings.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the change external dealer mappings.
     *
     * @param NovaUser|null $user
     * @param ExternalDealerMapping $ExternalDealerMapping
     * @return bool
     */
    public function view(?NovaUser $user, ExternalDealerMapping $ExternalDealerMapping): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create change external dealer mappingss.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the change external dealer mapping.
     *
     * @param NovaUser $user
     * @param ExternalDealerMapping $ExternalDealerMapping
     * @return bool
     */
    public function update(NovaUser $user, ExternalDealerMapping $ExternalDealerMapping): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the change external dealer mapping.
     *
     * @param NovaUser $user
     * @param ExternalDealerMapping $ExternalDealerMapping
     * @return bool
     */
    public function delete(NovaUser $user, ExternalDealerMapping $ExternalDealerMapping): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the change external dealer mapping.
     *
     * @param NovaUser $user
     * @param ExternalDealerMapping $ExternalDealerMapping
     * @return void
     */
    public function restore(NovaUser $user, ExternalDealerMapping $ExternalDealerMapping): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the change external dealer mappings.
     *
     * @param NovaUser $user
     * @param ExternalDealerMapping $ExternalDealerMapping
     * @return void
     */
    public function forceDelete(NovaUser $user, ExternalDealerMapping $ExternalDealerMapping): void
    {
        //
    }
}

