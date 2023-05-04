<?php

namespace App\Domains\UserTracking\Actions;

use App\Models\UserTracking;
use Carbon\Carbon;
use DB;
use Throwable;

class PopulateMissingWebsiteUserIdAction
{
    private Carbon $from;

    private Carbon $to;

    /**
     * @throws Throwable
     */
    public function execute(): void
    {
        $userTrackings = UserTracking::query()
            ->select(['visitor_id', 'website_user_id'])
            ->distinct()
            ->where('created_at', '>=', $this->from)
            ->where('created_at', '<=', $this->to)
            ->whereNotNull('website_user_id')
            ->get();

        DB::transaction(function () use ($userTrackings) {
            foreach ($userTrackings as $userTracking) {
                UserTracking::query()
                    ->where('visitor_id', $userTracking->visitor_id)
                    ->whereNull('website_user_id')
                    ->update([
                        'website_user_id' => $userTracking->website_user_id,
                    ]);
            }
        });
    }

    public function getFrom(): Carbon
    {
        return $this->from;
    }

    public function setFrom(Carbon $from): PopulateMissingWebsiteUserIdAction
    {
        $this->from = $from;

        return $this;
    }

    public function getTo(): Carbon
    {
        return $this->to;
    }

    public function setTo(Carbon $to): PopulateMissingWebsiteUserIdAction
    {
        $this->to = $to;

        return $this;
    }
}
