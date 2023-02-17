<?php

namespace App\Nova\Policies;

use App\Models\Feed\Mapping\Incoming\ApiEntityReference;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class ApiEntityReferencePolicy
 * @package App\Nova\Policies
 */
class ApiEntityReferencePolicy
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
     * Determine whether the user can view any reference.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the reference.
     *
     * @param NovaUser|null $user
     * @param ApiEntityReference $reference
     * @return bool
     */
    public function view(?NovaUser $user, ApiEntityReference $reference): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create references.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the reference.
     *
     * @param NovaUser $user
     * @param ApiEntityReference $reference
     * @return bool
     */
    public function update(NovaUser $user, ApiEntityReference $reference): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the reference.
     *
     * @param NovaUser $user
     * @param ApiEntityReference $reference
     * @return bool
     */
    public function delete(NovaUser $user, ApiEntityReference $reference): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the reference.
     *
     * @param NovaUser $user
     * @param ApiEntityReference $reference
     * @return void
     */
    public function restore(NovaUser $user, ApiEntityReference $reference): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the reference.
     *
     * @param NovaUser $user
     * @param ApiEntityReference $reference
     * @return void
     */
    public function forceDelete(NovaUser $user, ApiEntityReference $reference): void {
        //
    }
}

