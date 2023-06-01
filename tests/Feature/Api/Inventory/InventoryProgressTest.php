<?php

namespace Tests\Feature\Api\Inventory;

use App\Models\WebsiteUser\WebsiteUser;
use App\Models\WebsiteUser\WebsiteUserCache;
use Tests\Common\FeatureTestCase;

class InventoryProgressTest extends FeatureTestCase
{
    public function testSaveProgress(): void
    {
        $testData = [
            'a' => 'test',
        ];
        $user = WebsiteUser::factory()->create();
        $this->saveProgress($user->id, $testData);

        $this->assertDatabaseHas(WebsiteUserCache::class, [
            'website_user_id' => $user->id,
            'inventory_data->a' => 'test',
        ]);

        $progress = $this->loadProgress($user->id);
        $this->assertEquals($progress, $testData);
    }

    public function testUpdateProgress(): void
    {
        $firstData = [
            'first' => 'test',
        ];
        $secondData = [
            'second' => 'test',
        ];
        $user = WebsiteUser::factory()->create();
        $this->saveProgress($user->id, $firstData);
        $this->saveProgress($user->id, $secondData);

        $progress = $this->loadProgress($user->id);
        $this->assertEquals($progress, $secondData);
    }

    private function saveProgress($userId, $progress)
    {
        $user = WebsiteUser::find($userId);
        $this->actingAs($user, 'api')->post('api/inventory/progress', $progress);
    }

    private function loadProgress($userId)
    {
        $user = WebsiteUser::find($userId);

        return $this->actingAs($user, 'api')->get('api/inventory/progress')->json();
    }
}
