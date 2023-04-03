<?php

namespace App\Nova\Policies;

use App\Models\Feed\Uploads\FeedApiUpload;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class FeedApiUploadPolicy
 * @package App\Nova\Polices
 */
class FeedApiUploadPolicy
{
    use HandlesAuthorization;

    private const VALID_ROLES = ['Admin', 'Support', 'DataSupport'];

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
     * Determine whether the user can view any uploads.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the FeedApiUpload.
     *
     * @param NovaUser|null $user
     * @param FeedApiUpload $upload
     * @return bool
     */
    public function view(?NovaUser $user, FeedApiUpload $upload): bool
    {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create uploads.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the FeedApiUpload.
     *
     * @param NovaUser $user
     * @param FeedApiUpload $upload
     * @return bool
     */
    public function update(NovaUser $user, FeedApiUpload $upload): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the FeedApiUpload.
     *
     * @param NovaUser $user
     * @param FeedApiUpload $upload
     * @return bool
     */
    public function delete(NovaUser $user, FeedApiUpload $upload): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the FeedApiUpload.
     *
     * @param NovaUser $user
     * @param FeedApiUpload $upload
     * @return void
     */
    public function restore(NovaUser $user, FeedApiUpload $upload): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the FeedApiUpload.
     *
     * @param NovaUser $user
     * @param FeedApiUpload $upload
     * @return void
     */
    public function forceDelete(NovaUser $user, FeedApiUpload $upload): void
    {
        //
    }
}
