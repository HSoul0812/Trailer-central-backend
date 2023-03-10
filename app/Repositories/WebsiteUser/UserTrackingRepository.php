<?php

namespace App\Repositories\WebsiteUser;

use App\Models\UserTracking;
use Throwable;

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
        try {
            return auth('api')->user()?->id;
        } catch (Throwable) {
            return null;
        }
    }
}
