<?php

namespace App\Nova\Policies;

use App\Models\Inventory\EntityType;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class EntityTypePolicy
 * @package App\Nova\Policies
 */
class EntityTypePolicy
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
     * Determine whether the user can view any entity.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the entity.
     *
     * @param NovaUser|null $user
     * @param EntityType $type
     * @return bool
     */
    public function view(?NovaUser $user, EntityType $type): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create entity.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the entity.
     *
     * @param NovaUser $user
     * @param EntityType $type
     * @return bool
     */
    public function update(NovaUser $user, EntityType $type): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the entity.
     *
     * @param NovaUser $user
     * @param EntityType $type
     * @return bool
     */
    public function delete(NovaUser $user, EntityType $type): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the entity.
     *
     * @param NovaUser $user
     * @param EntityType $type
     * @return void
     */
    public function restore(NovaUser $user, EntityType $type): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the entity.
     *
     * @param NovaUser $user
     * @param EntityType $type
     * @return void
     */
    public function forceDelete(NovaUser $user, EntityType $type): void {
        //
    }
}

