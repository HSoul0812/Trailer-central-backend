<?php

namespace App\Repositories\WebsiteUser;

use App\Models\UserTracking;

interface UserTrackingRepositoryInterface
{
    public function create(array $params): UserTracking;
}
