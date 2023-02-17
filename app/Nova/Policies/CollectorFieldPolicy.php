<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use App\Models\Integration\Collector\CollectorFields;

use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class CollectorFieldPolicy
 * @package App\Nova\Policies
 */
class CollectorFieldPolicy
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
     * Determine whether the user can view any fields.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the field.
     *
     * @param NovaUser|null $user
     * @param CollectorFields $field
     * @return bool
     */
    public function view(?NovaUser $user, CollectorFields $field): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create fields.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the field.
     *
     * @param NovaUser $user
     * @param CollectorFields $field
     * @return bool
     */
    public function update(NovaUser $user, CollectorFields $field): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the field.
     *
     * @param NovaUser $user
     * @param CollectorFields $field
     * @return bool
     */
    public function delete(NovaUser $user, CollectorFields $field): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the field.
     *
     * @param NovaUser $user
     * @param CollectorFields $field
     * @return void
     */
    public function restore(NovaUser $user, CollectorFields $field): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the field.
     *
     * @param NovaUser $user
     * @param CollectorFields $field
     * @return void
     */
    public function forceDelete(NovaUser $user, CollectorFields $field): void
    {
        //
    }
}
