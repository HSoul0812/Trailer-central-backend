<?php

namespace App\Repositories\WebsiteUser;

use App\Domains\UserTracking\Actions\GetPageNameFromUrlAction;
use App\Models\UserTracking;
use Log;
use Throwable;

class UserTrackingRepository implements UserTrackingRepositoryInterface
{
    public function __construct(private GetPageNameFromUrlAction $getPageNameFromUrlAction)
    {
    }

    /**
     * @throws Throwable
     */
    public function create(array $params): UserTracking
    {
        if (!array_key_exists('website_user_id', $params)) {
            $params['website_user_id'] = $this->getWebsiteUserIdFromAuth();
        }

        if (empty($params['meta'])) {
            $params['meta'] = null;
        }

        $params['page_name'] = $this->getPageName($params['url']);

        try {
            return UserTracking::create($params);
        } catch (Throwable $exception) {
            Log::error(__METHOD__ . ': Failed to create a user tracking record - ' . $exception->getMessage());

            throw $exception;
        }
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

    private function getPageName(mixed $url): ?string
    {
        return $this->getPageNameFromUrlAction->execute($url);
    }
}
