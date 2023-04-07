<?php

namespace App\Repositories\WebsiteUser;

use App\Models\UserTracking;
use Throwable;

interface UserTrackingRepositoryInterface
{
    /**
     * @throws Throwable
     */
    public function create(array $params): UserTracking;
}
