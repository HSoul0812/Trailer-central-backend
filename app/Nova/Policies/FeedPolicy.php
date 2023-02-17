<?php

namespace App\Nova\Policies;

use App\Models\Feed\Feed;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class FeedPolicy
 * @package App\Nova\Policies
 */
class FeedPolicy
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
     * Determine whether the user can view any feed.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the feed.
     *
     * @param NovaUser|null $user
     * @param Feed $feed
     * @return bool
     */
    public function view(?NovaUser $user, Feed $feed): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create feeds.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the feed.
     *
     * @param NovaUser $user
     * @param Feed $feed
     * @return bool
     */
    public function update(NovaUser $user, Feed $feed): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the feed.
     *
     * @param NovaUser $user
     * @param Feed $feed
     * @return bool
     */
    public function delete(NovaUser $user, Feed $feed): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the feed.
     *
     * @param NovaUser $user
     * @param Feed $feed
     * @return void
     */
    public function restore(NovaUser $user, Feed $feed): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the feed.
     *
     * @param NovaUser $user
     * @param Feed $feed
     * @return void
     */
    public function forceDelete(NovaUser $user, Feed $feed): void {
        //
    }
}

