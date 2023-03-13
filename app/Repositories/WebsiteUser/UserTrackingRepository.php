<?php

namespace App\Repositories\WebsiteUser;

use App\Models\UserTracking;

class UserTrackingRepository implements UserTrackingRepositoryInterface
{
    public function create(array $params): UserTracking
    {
        if (!array_key_exists('website_user_id', $params)) {
            $params['website_user_id'] = $this->getWebsiteUserIdFromAuth();

            if ($params['website_user_id'] === null) {
                $params['website_user_id'] = $this->getWebsiteUserIdFromLatestRecordWithWebsiteUserId($params);
            }
        }

        if (empty($params['meta'])) {
            $params['meta'] = null;
        }

        return UserTracking::create($params);
    }

    private function getWebsiteUserIdFromAuth(): ?int
    {
        // Try to get the user id from the request, if there is no token,
        // or it's invalidated, then we save website_user_id as null
        return rescue(
            callback: fn() => auth('api')->user()?->id,
            report: false,
        );
    }

    private function getWebsiteUserIdFromLatestRecordWithWebsiteUserId(array $params): ?int
    {
        return UserTracking::query()
            ->where('visitor_id', $params['visitor_id'])
            ->whereNotNull('website_user_id')
            ->latest()
            ->first(['website_user_id'])
            ?->website_user_id;
    }
}
