<?php

namespace Tests\Feature\Observers;

use App\Jobs\ElasticSearch\Cache\InvalidateCacheJob;
use App\Models\User\AuthToken;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseRedisCache;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Laravel\Scout\Jobs\MakeSearchable;
use Mockery;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use App\Models\User\User;
use App\Models\Inventory\Inventory;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;

/**
 * @group DW
 * @group DW_ELASTICSSEARCH
 * @group DW_INVENTORY
 */
class InventoryObserverTest extends TestCase
{
    private $dealer;

    /** @var ResponseCacheKeyInterface */
    private $cacheKeyService;

    /** @var MockObject */
    private $searchResponseCache;

    /** @var MockObject */
    private $singleResponseCache;

    const INTEGRATIONS_ACCESS_TOKEN = '123';

    public function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        $this->dealer = factory(User::class)->create();
        $this->cacheKeyService = app(ResponseCacheKeyInterface::class);

        $this->singleResponseCache = Mockery::mock(ResponseCacheInterface::class);
        $this->searchResponseCache = Mockery::mock(ResponseCacheInterface::class);

        $inventoryCache = $this->createStub(InventoryResponseRedisCache::class);
        $inventoryCache->method('search')
            ->willReturn($this->searchResponseCache);
        $inventoryCache->method('single')
            ->willReturn($this->singleResponseCache);

        $this->instance(InventoryResponseCacheInterface::class, $inventoryCache);
    }

    public function test_it_invalidates_by_dealer_when_an_inventory_is_created()
    {
        Inventory::withoutSyncingToSearch(function () {
            $key = $this->cacheKeyService->deleteByDealer($this->dealer->dealer_id);
            $this->searchResponseCache->shouldReceive('forget')->once()->with($key);

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

            $dealerKey = $this->cacheKeyService->deleteByDealer($this->dealer->dealer_id);
            $singleKey = $this->cacheKeyService->deleteSingle($inventory->inventory_id);

            $this->searchResponseCache->shouldReceive('forget')->once()->with($dealerKey);
            $this->singleResponseCache->shouldReceive('forget')->once()->with($singleKey);

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

            $singleInCollectionKey = $this->cacheKeyService->deleteSingleFromCollection($inventory->inventory_id);
            $singleKey = $this->cacheKeyService->deleteSingle($inventory->inventory_id);

            $this->searchResponseCache->shouldReceive('forget')->once()->with($singleInCollectionKey);
            $this->singleResponseCache->shouldReceive('forget')->once()->with($singleKey);

            $inventory->delete();
        });
    }

    public function test_it_does_not_trigger_invalidations_if_cache_is_disabled_via_env()
    {
        Config::set('cache.inventory', 0);

        Inventory::withoutSyncingToSearch(function () {
            $this->searchResponseCache->shouldNotReceive('forget');
            $this->singleResponseCache->shouldNotReceive('forget');

            $inventory = factory(Inventory::class)->create(['dealer_id' => $this->dealer->dealer_id]);
            $inventory->update(['title' => Str::random(10)]);
            $inventory->delete();
        });
    }

    public function test_it_does_not_trigger_invalidations_for_requests_from_integrations()
    {
        Config::set('integrations.inventory_cache_auth.credentials.access_token', self::INTEGRATIONS_ACCESS_TOKEN);
        Config::set('cache.inventory', true); // to ensure the cache is enable for this particular test case

        $this->searchResponseCache->shouldNotReceive('forget');
        $this->singleResponseCache->shouldNotReceive('forget');

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

        Bus::assertNotDispatched(InvalidateCacheJob::class);
        Bus::assertNotDispatched(MakeSearchable::class);
    }

    public function test_it_triggers_invalidations_for_requests_not_from_integrations()
    {
        Config::set('integrations.inventory_cache_auth.credentials.access_token', self::INTEGRATIONS_ACCESS_TOKEN);

        Inventory::withoutSyncingToSearch(function () {
            $inventory = Inventory::withoutEvents(function () {
                return Inventory::create(factory(Inventory::class)->make(['dealer_id' => $this->dealer->dealer_id])->toArray());
            });

            $dealerKey = $this->cacheKeyService->deleteByDealer($this->dealer->dealer_id);
            $singleKey = $this->cacheKeyService->deleteSingle($inventory->inventory_id);

            $this->searchResponseCache->shouldReceive('forget')->once()->with($dealerKey);
            $this->singleResponseCache->shouldReceive('forget')->once()->with($singleKey);

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
