<?php

namespace App\Nova\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DealerIncomingPendingMappingPolicy {
    
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the post.
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     * @return mixed
     */
    public function update(User $user)
    {
        return false;
    }
    
    public function create(User $user)
    {
        return false;
    }
    
    public function delete(User $user)
    {
        return false;
    }
    
}
