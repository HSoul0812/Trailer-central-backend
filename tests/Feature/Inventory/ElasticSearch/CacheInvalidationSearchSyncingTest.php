<?php

namespace Tests\Feature\Inventory\ElasticSearch;

use App\Jobs\ElasticSearch\Cache\InvalidateCacheJob;
use App\Jobs\Website\ReIndexInventoriesByDealersJob;
use App\Models\Inventory\Inventory;
use App\Models\User\AuthToken;
use App\Models\User\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Laravel\Scout\Jobs\MakeSearchable;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_INVENTORY
 * @group DW_ELASTICSEARCH
 */
class CacheInvalidationSearchSyncingTest extends TestCase
{
    private const  INTEGRATIONS_ACCESS_TOKEN = '123';

    /** @var User */
    private $dealer;

    /** @var AuthToken[] */
    private $tokens = [];

    /** @var Inventory[] */
    private $inventories = [];

    public function test_it_does_not_dispatch_jobs_when_requests_from_integrations(): void
    {
        Config::set('cache.inventory', true); // this should not be consider due the request comes from integration process

        $inventory = $this->getInventoryWithoutTriggerEvents();
        $authToken = $this->getAuthToken();

        Config::set('integrations.inventory_cache_auth.credentials.access_token', $authToken->access_token);

        $newTitle = Str::random(20);
        $response = $this
            ->withHeaders([
                'access-token' => $authToken->access_token
            ])
            ->post('/api/inventory/'.$inventory->inventory_id, ['title' => $newTitle]);

        $response->assertStatus(200);

        $this->assertDatabaseHas(Inventory::getTableName(), [
            'inventory_id' => $inventory->inventory_id,
            'title' => $newTitle
        ]);

        Bus::assertNotDispatched(InvalidateCacheJob::class);
        Bus::assertNotDispatched(MakeSearchable::class);
    }

    public function test_it_doesnt_invalidate_cache_when_cache_is_disabled(): void
    {
        Config::set('cache.inventory', false);

        $inventory = $this->getInventoryWithoutTriggerEvents();
        $authToken = $this->getAuthToken();

        $newTitle = Str::random(20);

        $response = $this
            ->withHeaders(['access-token' => $authToken->access_token])
            ->post('/api/inventory/'.$inventory->inventory_id, ['title' => $newTitle]);

        $response->assertStatus(200);

        $this->assertDatabaseHas(Inventory::getTableName(), [
            'inventory_id' => $inventory->inventory_id,
            'title' => $newTitle
        ]);

        Bus::assertNotDispatched(InvalidateCacheJob::class);
        Bus::assertDispatchedTimes(MakeSearchable::class, 1); // it should still being indexing
    }

    public function test_it_dispatch_jobs_when_requests_not_from_integrations(): void
    {
        Config::set('cache.inventory', true);

        $inventory = $this->getInventoryWithoutTriggerEvents();
        $authToken = $this->getAuthToken();

        $newTitle = Str::random(20);

        $response = $this
            ->withHeaders(['access-token' => $authToken->access_token])
            ->post('/api/inventory/'.$inventory->inventory_id, ['title' => $newTitle]);

        $response->assertStatus(200);

        $this->assertDatabaseHas(Inventory::getTableName(), [
            'inventory_id' => $inventory->inventory_id,
            'title' => $newTitle
        ]);

        Bus::assertDispatchedTimes(InvalidateCacheJob::class, 2); // this should be fixed to only dispatch it once
        Bus::assertDispatchedTimes(MakeSearchable::class, 1);
    }

    public function test_it_dispatch_jobs_by_dealer_when_direct_endpoint_is_use(): void
    {
        Config::set('cache.inventory', false); // no matter if cache is disabled, it should invalidate

        Config::set('integrations.inventory_cache_auth.credentials.access_token', self::INTEGRATIONS_ACCESS_TOKEN);

        Inventory::disableCacheInvalidationAndSearchSyncing();

        $response = $this
            ->withHeaders(['access-token' => self::INTEGRATIONS_ACCESS_TOKEN])
            ->post('api/inventory/cache/invalidate/dealer', ['dealer_id' => [
                $this->dealer->dealer_id,
                $this->dealer->dealer_id // twice to ensure it still enqueueing only once
            ]]);

        $response->assertStatus(202);

        Bus::assertDispatchedTimes(InvalidateCacheJob::class, 1);
        Bus::assertDispatchedTimes(ReIndexInventoriesByDealersJob::class, 1);
    }

    public function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Inventory::withoutCacheInvalidationAndSearchSyncing(function () {
            $this->dealer = factory(User::class)->create();
        });
    }

    public function tearDown(): void
    {
        foreach ($this->tokens as $token) {
            $token->delete();
        }

        foreach ($this->inventories as $inventory) {
            $inventory->delete();
        }

        $this->dealer->delete();

        parent::tearDown();
    }

    private function getInventoryWithoutTriggerEvents(): Inventory
    {
        $inventory = Inventory::withoutCacheInvalidationAndSearchSyncing(function (): Inventory {
            return Inventory::create(factory(Inventory::class)->make(['dealer_id' => $this->dealer->dealer_id])->toArray());
        });

        $this->inventories[] = $inventory;

        return $inventory;
    }

    private function getAuthToken(): AuthToken
    {
        $token = factory(AuthToken::class)->create(['user_id' => $this->dealer->dealer_id]);
        $this->tokens[] = $token;

        return $token;
    }
}
