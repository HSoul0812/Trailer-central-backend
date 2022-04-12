<?php

namespace App\Nova\Policies;

use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventoryPolicy {
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return false
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * @param User $user
     * @return false
     */
    public function delete(User $user)
    {
        return false;
    }

}
