<?php

namespace Tests\Unit\Services\Parts;

use App\Events\Parts\PartQtyUpdated;
use App\Models\Parts\BinQuantity;
use App\Models\Parts\Part;
use App\Repositories\Parts\CycleCountRepositoryInterface;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Services\Parts\PartService;
use Illuminate\Support\Facades\Event;
use Mockery\Mock;
use Tests\TestCase;

class PartServiceTest extends TestCase
{
    public function testUpdateWillDispatchAuditLog()
    {
        // test data
        $partsData = [
            'id' => 1253300,
            'title' => 'Sample1',
        ];
        $bins = [
            ['bin_id' => 2, 'old_quantity' => 10, 'quantity' => 15],
            ['bin_id' => 3, 'old_quantity' => 12, 'quantity' => 9],
        ];

        //
        $this->mock(CycleCountRepositoryInterface::class, function($mock) {
            $mock->shouldReceive('create');
        });

        //
        $this->mock(BinQuantity::class, function ($mock) {
            /** @var Mock $mock */
            $mock->shouldReceive('where')->andReturns($mock);
            $mock->shouldReceive('first')->andReturns(new BinQuantity([
                'part_id' => 1253300,
                'bin_id' => 2,
                'qty' => 15,
            ]));
        });

        //
        $this->mock(PartRepositoryInterface::class, function ($mock) {
            $returnPart = new Part();
            $returnPart->id = 1253300;
            $mock->shouldReceive('update')
                ->once()
                ->andReturns($returnPart);
        });

        Event::fake();

        //
        /** @var PartService $service */
        $service = app(PartService::class);
        $service->update($partsData, $bins);

        // assertions
        Event::assertDispatched(PartQtyUpdated::class, function ($event) {
            return $event->part->id == 1253300;
        });
        Event::assertDispatched(PartQtyUpdated::class, function ($event) {
            return $event->binQuantity->bin_id == 2;
        });
        Event::assertDispatched(PartQtyUpdated::class, function ($event) {
            return $event->details['quantity'] == 5;
        });
        Event::assertDispatched(PartQtyUpdated::class, function ($event) {
            return $event->details['description'] == "Part updated";
        });
    }

    public function testCreateWillDispatchAuditLog()
    {
        // test data
        $partsData = [
            'id' => 1253300,
            'title' => 'Sample1',
        ];
        $bins = [
            ['bin_id' => 2, 'old_quantity' => 10, 'quantity' => 15],
            ['bin_id' => 3, 'old_quantity' => 12, 'quantity' => 9],
        ];

        //
        $this->mock(CycleCountRepositoryInterface::class, function($mock) {
            $mock->shouldReceive('create');
        });

        //
        $this->mock(BinQuantity::class, function ($mock) {
            /** @var Mock $mock */
            $mock->shouldReceive('where')->andReturns($mock);
            $mock->shouldReceive('first')->andReturns(new BinQuantity([
                'part_id' => 1253300,
                'bin_id' => 2,
                'qty' => 15,
            ]));
        });

        //
        $this->mock(PartRepositoryInterface::class, function ($mock) {
            $returnPart = new Part();
            $returnPart->id = 1253300;
            $mock->shouldReceive('create')
                ->once()
                ->andReturns($returnPart);
        });

        Event::fake();

        //
        /** @var PartService $service */
        $service = app(PartService::class);
        $service->create($partsData, $bins);

        // assertions
        Event::assertDispatched(PartQtyUpdated::class, function ($event) {
            return $event->part->id == 1253300;
        });
        Event::assertDispatched(PartQtyUpdated::class, function ($event) {
            return $event->binQuantity->bin_id == 2;
        });
        Event::assertDispatched(PartQtyUpdated::class, function ($event) {
            return $event->details['quantity'] == 5;
        });
        Event::assertDispatched(PartQtyUpdated::class, function ($event) {
            return $event->details['description'] == "Part created";
        });
    }
}
