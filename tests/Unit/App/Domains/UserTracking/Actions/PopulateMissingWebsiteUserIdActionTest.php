<?php

namespace Tests\Unit\App\Domains\UserTracking\Actions;

use App\Domains\UserTracking\Actions\PopulateMissingWebsiteUserIdAction;
use App\Models\UserTracking;
use App\Models\WebsiteUser\WebsiteUser;
use Str;
use Tests\Common\TestCase;
use Throwable;

class PopulateMissingWebsiteUserIdActionTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testItCanSetMissingWebsiteUserId()
    {
        $websiteUser1 = WebsiteUser::factory()->create();
        $websiteUser2 = WebsiteUser::factory()->create();

        $visitorId1 = Str::uuid();
        $visitorId2 = Str::uuid();

        UserTracking::factory()->count(10)->create([
            'visitor_id' => $visitorId1,
        ]);
        UserTracking::factory()->create([
            'visitor_id' => $visitorId1,
            'website_user_id' => $websiteUser1->id,
        ]);

        UserTracking::factory()->count(5)->create([
            'visitor_id' => $visitorId2,
        ]);
        UserTracking::factory()->create([
            'visitor_id' => $visitorId2,
            'website_user_id' => $websiteUser2->id,
        ]);

        $action = resolve(PopulateMissingWebsiteUserIdAction::class);

        $action
            ->setFrom(now()->startOfDay())
            ->setTo(now()->endOfDay())
            ->execute();

        $websiteUser1Count = UserTracking::query()
            ->where('visitor_id', $visitorId1)
            ->where('website_user_id', $websiteUser1->id)
            ->count();
        $websiteUser2Count = UserTracking::query()
            ->where('visitor_id', $visitorId2)
            ->where('website_user_id', $websiteUser2->id)
            ->count();

        $this->assertEquals(11, $websiteUser1Count);
        $this->assertEquals(6, $websiteUser2Count);
    }
}
