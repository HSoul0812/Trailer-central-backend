<?php

namespace Tests\Integration\Services\ElasticSearch\Cache\RedisResponseCache;

use App\Models\FeatureFlag;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
//use App\Repositories\FeatureFlagRepository;
use App\Repositories\FeatureFlagRepositoryInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseRedisCache;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;
use Illuminate\Support\Facades\Bus;
//use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_INVENTORY
 * @group DW_ELASTICSEARCH
 *
 * @covers \App\Services\ElasticSearch\Cache\RedisResponseCache::forget
 */
class ForgetTest extends TestCase
{
    /** @var User */
    private $dealer;

    /** @var ResponseCacheKeyInterface */
    private $cacheKeyService;

    /** @var MockObject|ResponseCacheInterface */
    private $searchResponseCache;

    /** @var MockObject|ResponseCacheInterface */
    private $singleResponseCache;

    public function test_it_forgets_by_dealer_when_an_inventory_is_created(): void
    {
        Inventory::enableCacheInvalidation();

        $key = $this->cacheKeyService->deleteByDealer($this->dealer->dealer_id);
        $this->searchResponseCache->expects('forget')->with($key);

        $inventory = factory(Inventory::class)->create(['dealer_id' => $this->dealer->dealer_id]);
        $inventory->deleteQuietly();
    }

    public function test_it_forgets_by_dealer_and_by_single_when_an_inventory_is_updated(): void
    {
        $inventory = $this->getInventoryWithoutTriggerEvents();

        $dealerKey = $this->cacheKeyService->deleteByDealer($this->dealer->dealer_id);
        $singleKey = $this->cacheKeyService->deleteSingle($inventory->inventory_id, $this->dealer->dealer_id);

        $this->searchResponseCache->expects('forget')->with($dealerKey);
        $this->singleResponseCache->expects('forget')->with($singleKey);

        $inventory->update(['title' => Str::random(10)]);

        $inventory->deleteQuietly();
    }

    public function test_it_forgets_by_single_in_collection_and_by_single_when_an_inventory_is_deleted(): void
    {
        $inventory = $this->getInventoryWithoutTriggerEvents();

        $singleInCollectionKey = $this->cacheKeyService->deleteSingleFromCollection($inventory->inventory_id);
        $singleKey = $this->cacheKeyService->deleteSingle($inventory->inventory_id, $inventory->dealer_id);

        $this->searchResponseCache->expects('forget')->with($singleInCollectionKey);
        $this->singleResponseCache->expects('forget')->with($singleKey);

        $inventory->delete();
    }

    public function test_it_does_not_forget_if_cache_is_disabled(): void
    {
        Inventory::disableCacheInvalidation();

        $this->mock(ResponseCacheInterface::class, function ($mock) {
            $mock->shouldNotReceive('forget');
        });

        $inventory = factory(Inventory::class)->create(['dealer_id' => $this->dealer->dealer_id]);
        $inventory->update(['title' => Str::random(10)]);
        $inventory->delete();
    }

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

        app(FeatureFlagRepositoryInterface::class)->set(
            new FeatureFlag(['code' => 'inventory-sdk-cache', 'is_enabled' => true])
        );
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
