<?php

namespace Tests\Feature\Inventory\ElasticSearch;

use App\Jobs\ElasticSearch\Cache\InvalidateCacheJob;
use App\Jobs\Website\ReIndexInventoriesByDealersJob;
use App\Models\Inventory\Inventory;
use App\Models\User\AuthToken;
use App\Models\User\User;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;
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

    /** @var ResponseCacheKeyInterface */
    private $cacheKeyService;

    public function test_it_does_not_dispatch_jobs_when_requests_from_integrations(): void
    {
        Inventory::disableCacheInvalidationAndSearchSyncing();

        $inventory = Inventory::create(factory(Inventory::class)->make(['dealer_id' => $this->dealer->dealer_id])->toArray());
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

    public function test_it_dispatch_jobs_when_requests_not_from_integrations(): void
    {
        $authToken = factory(AuthToken::class)->create(['user_id' => $this->dealer->dealer_id]);
        $inventory = $this->getInventoryWithoutTriggerEvents();

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

        Bus::assertDispatched(InvalidateCacheJob::class);
        Bus::assertDispatched(MakeSearchable::class);
    }

    public function test_it_dispatch_jobs_by_dealer_when_direct_endpoint_is_use(): void
    {
        Config::set('integrations.inventory_cache_auth.credentials.access_token', self::INTEGRATIONS_ACCESS_TOKEN);

        Inventory::disableCacheInvalidationAndSearchSyncing();

        $response = $this->withHeaders([
            'access-token' => self::INTEGRATIONS_ACCESS_TOKEN
        ])
            ->post('api/inventory/cache/invalidate/dealer', ['dealer_id' => [$this->dealer->dealer_id]]);

        $response->assertStatus(202);

        Bus::assertDispatched(InvalidateCacheJob::class);
        Bus::assertDispatched(ReIndexInventoriesByDealersJob::class);
    }

    public function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        $this->dealer = factory(User::class)->create();

        $this->cacheKeyService = app(ResponseCacheKeyInterface::class);

        Inventory::enableCacheInvalidationAndSearchSyncing();
    }

    public function tearDown(): void
    {
        $this->dealer->delete();

        parent::tearDown();
    }

    public function getInventoryWithoutTriggerEvents(): Inventory
    {
        return Inventory::withoutCacheInvalidationAndSearchSyncing(function (): Inventory {
            return Inventory::create(factory(Inventory::class)->make(['dealer_id' => $this->dealer->dealer_id])->toArray());
        });
    }
}
