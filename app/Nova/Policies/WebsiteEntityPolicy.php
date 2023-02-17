<?php
namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use App\Models\Website\Entity as WebsiteEntity;

use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class WebsiteEntity
 * @package App\Nova\Policies
 */
class WebsiteEntityPolicy
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
     * Determine whether the user can view any change website entities.
     *
     * @param  NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }
    /**
     * Determine whether the user can view the change website entities.
     *
     * @param NovaUser|null $user
     * @param WebsiteEntity $entity
     * @return bool
     */
    public function view(?NovaUser $user, WebsiteEntity $entity): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }
    /**
     * Determine whether the user can create change website entities.
     *
     * @param  NovaUser  $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }
    /**
     * Determine whether the user can update the change website entities.
     *
     * @param NovaUser $user
     * @param WebsiteEntity $entity
     * @return bool
     */
    public function update(NovaUser $user, WebsiteEntity $entity): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }
    /**
     * Determine whether the user can delete the change website entities.
     *
     * @param NovaUser $user
     * @param WebsiteEntity $entity
     * @return bool
     */
    public function delete(NovaUser $user, WebsiteEntity $entity): bool
    {
        return false;
    }
    /**
     * Determine whether the user can restore the change website entities.
     *
     * @param NovaUser $user
     * @param WebsiteEntity $entity
     * @return void
     */
    public function restore(NovaUser $user, WebsiteEntity $entity): void
    {
        //
    }
    /**
     * Determine whether the user can permanently delete the change website entities.
     *
     * @param NovaUser $user
     * @param WebsiteEntity $entity
     * @return void
     */
    public function forceDelete(NovaUser $user, WebsiteEntity $entity): void
    {
        //
    }
}
