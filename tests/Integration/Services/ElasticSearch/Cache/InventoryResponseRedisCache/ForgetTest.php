<?php

namespace Tests\Integration\Services\ElasticSearch\Cache\InventoryResponseRedisCache;

use App\Models\FeatureFlag;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
use App\Repositories\FeatureFlagRepositoryInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseRedisCache;
use App\Services\ElasticSearch\Cache\ResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;
use Illuminate\Support\Facades\Bus;
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

    /**
     * @throws \Exception when deletion was not possible
     */
    public function test_it_forgets_by_dealer_when_an_inventory_is_created(): void
    {
        Inventory::enableCacheInvalidation();

        /** @var MockObject|InventoryResponseCacheInterface $service */
        $service = app(InventoryResponseCacheInterface::class);

        $key = $this->cacheKeyService->deleteByDealer($this->dealer->dealer_id);
        $service->expects($this->once())->method('forget')->with([$key]);

        $inventory = factory(Inventory::class)->create(['dealer_id' => $this->dealer->dealer_id]);
        $inventory->deleteQuietly();
    }

    /**
     * @throws \Exception when deletion was not possible
     */
    public function test_it_forgets_by_dealer_and_by_single_when_an_inventory_is_updated(): void
    {
        $inventory = $this->getInventoryWithoutTriggerEvents();

        $dealerKey = $this->cacheKeyService->deleteByDealer($this->dealer->dealer_id);
        $singleKey = $this->cacheKeyService->deleteSingle($inventory->inventory_id, $this->dealer->dealer_id);

        /** @var MockObject|InventoryResponseCacheInterface $service */
        $service = app(InventoryResponseCacheInterface::class);

        $service->expects($this->once())->method('forget')->with([$dealerKey, $singleKey]);

        $inventory->update(['title' => Str::random(10)]);

        $inventory->deleteQuietly();
    }

    /**
     * @throws \Exception when deletion was not possible
     */
    public function test_it_forgets_by_single_in_collection_and_by_single_when_an_inventory_is_deleted(): void
    {
        $inventory = $this->getInventoryWithoutTriggerEvents();

        $singleInCollectionKey = $this->cacheKeyService->deleteSingleFromCollection($inventory->inventory_id);
        $singleKey = $this->cacheKeyService->deleteSingle($inventory->inventory_id, $inventory->dealer_id);

        /** @var MockObject|InventoryResponseCacheInterface $service */
        $service = app(InventoryResponseCacheInterface::class);

        $service->expects($this->once())->method('forget')->with([$singleInCollectionKey, $singleKey]);

        $inventory->delete();
    }

    /**
     * @throws \Exception when deletion was not possible
     */
    public function test_it_does_not_forget_if_cache_is_disabled(): void
    {
        Inventory::disableCacheInvalidation();

        $this->mock(ResponseCacheInterface::class, function ($mock) {
            $mock->shouldNotReceive('forget');
        });

        /** @var MockObject|InventoryResponseCacheInterface $service */
        $service = app(InventoryResponseCacheInterface::class);
        $service->expects($this->never())->method('forget');

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

        $inventoryCache = $this->getMockBuilder(InventoryResponseRedisCache::class)
            ->setConstructorArgs([
                $this->cacheKeyService,
                Mockery::mock(ResponseCacheInterface::class),
                Mockery::mock(ResponseCacheInterface::class)
            ])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

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
