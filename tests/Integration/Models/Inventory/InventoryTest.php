<?php

namespace Tests\Integration\Models\Inventory;

use App\Domains\Scout\Jobs\ExceptionableMakeSearchable;
use App\Jobs\ElasticSearch\Cache\InvalidateCacheJob;
use App\Models\Inventory\Inventory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Models\Inventory\Inventory
 *
 * @group DW
 * @group DW_BULK
 * @group DW_BULK_INVENTORY
 * @group DW_BULK_UPLOAD_INVENTORY
 */
class InventoryTest extends TestCase
{
    use WithFaker;

    /**
     * Test that SUT will not dispatch any jobs when `withoutInvalidationAndSyncingToSearch` is used,
     * also, it will ensure that after `withoutInvalidationAndSyncingToSearch` was used, the jobs will be dispatched
     * once again (we supposed always cache is enabled)
     *
     * @covers ::withoutInvalidationAndSyncingToSearch
     * @covers ::update
     * @covers ::delete
     *
     * @return void
     * @throws \Exception when deletion fails
     */
    public function testItWillNotDispatchAnyJobAsExpected(): void
    {
        Inventory::withoutInvalidationAndSyncingToSearch(function () {
            $inventory = factory(Inventory::class)->create();
            $inventory->update(['description' => $this->faker->sentence(4)]);
            $inventory->delete();
        });

        Bus::assertNotDispatched(InvalidateCacheJob::class);

        // we need to fix this, the right class should be `MakeSearchable`
        Bus::assertNotDispatched(ExceptionableMakeSearchable::class);

        $inventory = factory(Inventory::class)->create();
        $inventory->update(['description' => $this->faker->sentence(4)]);
        $inventory->delete();

        // we need to fix this, the right class should be `MakeSearchable`
        Bus::assertDispatchedTimes(ExceptionableMakeSearchable::class, 2);

        Bus::assertDispatchedTimes(InvalidateCacheJob::class, 3);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('cache.inventory', true);

        Bus::fake();
    }
}
