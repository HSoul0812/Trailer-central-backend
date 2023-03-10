<?php

namespace App\Repositories\WebsiteUser;

use App\Models\UserTracking;

class UserTrackingRepository implements UserTrackingRepositoryInterface
{
    public function create(array $params): UserTracking
    {
        if (!array_key_exists('website_user_id', $params)) {
            $params['website_user_id'] = $this->getWebsiteUserId();
        }

        return UserTracking::create($params);
    }

    private function getWebsiteUserId(): ?int
    {
        // Try to get the user id from the request, if there is no token,
        // or it's invalidated, then we save website_user_id as null
        return rescue(
            callback: fn() =>auth('api')->user()?->id,
            report: false,
        );
    }
}
