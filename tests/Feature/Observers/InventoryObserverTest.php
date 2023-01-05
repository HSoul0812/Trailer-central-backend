<?php

namespace Tests\Feature\Observers;

use App\Models\User\AuthToken;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\User\User;
use App\Models\Inventory\Inventory;
use Illuminate\Support\Facades\Queue;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;

class InventoryObserverTest extends TestCase
{
    private $dealer;

    /** @var ResponseCacheKeyInterface */
    private $cacheKeyService;

    const INTEGRATIONS_ACCESS_TOKEN = '123';

    public function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        $this->dealer = factory(User::class)->create();
        $this->cacheKeyService = app(ResponseCacheKeyInterface::class);
    }

    public function test_it_invalidates_by_dealer_when_an_inventory_is_created()
    {
        Inventory::withoutSyncingToSearch(function () {
            $this->mock(ResponseCacheInterface::class, function ($mock) {
                $key = $this->cacheKeyService->deleteByDealer($this->dealer->dealer_id);
                $mock->shouldReceive('forget')->once()->with($key);
            });

            $inventory = factory(Inventory::class)->create(['dealer_id' => $this->dealer->dealer_id]);
            $inventory->deleteQuietly();
        });
    }

    public function test_it_invalidates_by_dealer_and_by_single_when_an_inventory_is_updated()
    {
        Inventory::withoutSyncingToSearch(function () {
            $inventory = Inventory::withoutEvents(function () {
                return Inventory::create(factory(Inventory::class)->make(['dealer_id' => $this->dealer->dealer_id])->toArray());
            });

            $this->mock(ResponseCacheInterface::class, function ($mock) use ($inventory) {
                $dealerKey = $this->cacheKeyService->deleteByDealer($this->dealer->dealer_id);
                $singleKey = $this->cacheKeyService->deleteSingle($inventory->inventory_id);

                $mock->shouldReceive('forget')->once()->with($dealerKey, $singleKey);
            });

            $inventory->update(['title' => Str::random(10)]);

            $inventory->deleteQuietly();
        });
    }

    public function test_it_invalidates_by_single_in_collection_and_by_single_when_an_inventory_is_deleted()
    {
        Inventory::withoutSyncingToSearch(function () {
            $inventory = Inventory::withoutEvents(function () {
                return Inventory::create(factory(Inventory::class)->make(['dealer_id' => $this->dealer->dealer_id])->toArray());
            });

            $this->mock(ResponseCacheInterface::class, function ($mock) use ($inventory) {
                $singleInCollectionKey = $this->cacheKeyService->deleteSingleFromCollection($inventory->inventory_id);
                $singleKey = $this->cacheKeyService->deleteSingle($inventory->inventory_id);
                $mock->shouldReceive('forget')->once()->with($singleInCollectionKey, $singleKey);
            });

            $inventory->delete();
        });
    }

    public function test_it_does_not_trigger_invalidations_if_cache_is_disabled_via_env()
    {
        Config::set('cache.inventory', 0);

        Inventory::withoutSyncingToSearch(function () {
            $this->mock(ResponseCacheInterface::class, function ($mock) {
                $mock->shouldNotReceive('forget');
            });

            $inventory = factory(Inventory::class)->create(['dealer_id' => $this->dealer->dealer_id]);
            $inventory->update(['title' => Str::random(10)]);
            $inventory->delete();
        });
    }

    public function test_it_does_not_trigger_invalidations_for_requests_from_integrations()
    {
        Config::set('integrations.inventory_cache_auth.credentials.access_token', self::INTEGRATIONS_ACCESS_TOKEN);

        Inventory::withoutSyncingToSearch(function () {
            $this->mock(ResponseCacheInterface::class, function ($mock) {
                $mock->shouldNotReceive('forget');
            });

            $inventory = Inventory::withoutEvents(function () {
                return Inventory::create(factory(Inventory::class)->make(['dealer_id' => $this->dealer->dealer_id])->toArray());
            });

            $authToken = factory(AuthToken::class)->create(['user_id' => $this->dealer->dealer_id]);

            $newTitle = Str::random(20);
            $response = $this->withHeaders([
                'access-token' => $authToken->access_token,
                'x-client-id' => self::INTEGRATIONS_ACCESS_TOKEN
            ])
                ->post('/api/inventory/' . $inventory->inventory_id, ['title' => $newTitle]);

            $response->assertStatus(200);

            $this->assertDatabaseHas(Inventory::getTableName(), [
                'inventory_id' => $inventory->inventory_id,
                'title' => $newTitle
            ]);

            $inventory->deleteQuietly();
            $authToken->delete();
        });
    }

    public function test_it_triggers_invalidations_for_requests_not_from_integrations()
    {
        Config::set('integrations.inventory_cache_auth.credentials.access_token', self::INTEGRATIONS_ACCESS_TOKEN);

        Inventory::withoutSyncingToSearch(function () {
            $inventory = Inventory::withoutEvents(function () {
                return Inventory::create(factory(Inventory::class)->make(['dealer_id' => $this->dealer->dealer_id])->toArray());
            });

            $this->mock(ResponseCacheInterface::class, function ($mock) use ($inventory) {
                $dealerKey = $this->cacheKeyService->deleteByDealer($this->dealer->dealer_id);
                $singleKey = $this->cacheKeyService->deleteSingle($inventory->inventory_id);

                $mock->shouldReceive('forget')->once()->with($dealerKey, $singleKey);
            });

            $authToken = factory(AuthToken::class)->create(['user_id' => $this->dealer->dealer_id]);

            $newTitle = Str::random(20);
            $response = $this->withHeaders([
                'access-token' => $authToken->access_token
            ])
                ->post('/api/inventory/' . $inventory->inventory_id, ['title' => $newTitle]);

            $response->assertStatus(200);

            $this->assertDatabaseHas(Inventory::getTableName(), [
                'inventory_id' => $inventory->inventory_id,
                'title' => $newTitle
            ]);

            $inventory->deleteQuietly();
            $authToken->delete();
        });
    }

    public function tearDown(): void
    {
        $this->dealer->delete();
        parent::tearDown();
    }

}
