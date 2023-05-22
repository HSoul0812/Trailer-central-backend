<?php

namespace App\Repositories\WebsiteUser;

use App\Domains\UserTracking\Actions\GetPageNameFromUrlAction;
use App\Domains\UserTracking\Jobs\ProcessMonthlyInventoryImpression;
use App\Domains\UserTracking\Types\UserTrackingEvent;
use App\Models\UserTracking;
use Arr;
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

        if (!isset($params['page_name'])) {
            $params['page_name'] = $this->getPageName($params['url']);
        }

        $params['ip_address'] = $this->getUserIpAddress();

        // We don't want to process this record for location if the IP
        // address doesn't exist, or if the IP address is in the ignore
        // location processing list
        if (empty($params['ip_address']) || in_array($params['ip_address'], UserTracking::IGNORE_LOCATION_PROCESSING_IP_ADDRESSES)) {
            $params['location_processed'] = true;
        }

        try {
            $userTracking = UserTracking::create($params);

            $this->dispatchProcessMonthlyInventoryImpressionJob($userTracking);

            return $userTracking;
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
            callback: fn () => auth('api')->user()?->id,
            report: false,
        );
    }

    private function getPageName(mixed $url): ?string
    {
        return $this->getPageNameFromUrlAction->execute($url);
    }

    /**
     * We need to use our custom IP address getter because we serve
     * production server under the AWS load balancers.
     *
     * This makes the actual user's IP address lives in the HTTP_X_FORWARDED_FOR
     * variable and the AWS load balancer IP address in the REMOTE_ADDR variable
     * which Laravel first read the IP from.
     */
    private function getUserIpAddress(): ?string
    {
        $ipVariables = [
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        ];

        foreach ($ipVariables as $ipVariable) {
            $ipAddress = Arr::get($_SERVER, $ipVariable);

            if ($ipAddress !== null) {
                return $ipAddress;
            }
        }

        // As a last resort, we will use Laravel's ip() method
        // , so it can read from any server variable that we don't
        // have on the priority list above
        return request()->ip();
    }

    private function dispatchProcessMonthlyInventoryImpressionJob(UserTracking $userTracking): void
    {
        // Do not dispatch the job if the page_name is not in the valid list
        if (!in_array($userTracking->page_name, GetPageNameFromUrlAction::PAGE_NAMES)) {
            return;
        }

        // Do not dispatch if the event is not impression
        if ($userTracking->event !== UserTrackingEvent::IMPRESSION) {
            return;
        }

        ProcessMonthlyInventoryImpression::dispatch($userTracking);
    }
}
