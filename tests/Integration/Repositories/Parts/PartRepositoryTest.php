<?php

namespace Tests\Integration\Repositories\Parts;

use App\Events\Parts\PartQtyUpdated;
use App\Models\Parts\Bin;
use App\Models\Parts\BinQuantity;
use App\Models\Parts\Part;
use App\Repositories\Parts\PartRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery\Mock;
use Tests\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PartRepositoryTest extends TestCase
{
    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     * @throws \Exception
     */
    public function testCreateWillFireAuditLogEvent()
    {
        $testPart = new Part();
        $testPart->id = 123451;

        $testBinQty = new BinQuantity();
        $testBinQty->bin_id = 999990;
        $testBinQty->part_id = 123451;
        $testBinQty->qty = 100;

        $testData = [
            'id' => 123451,
            'dealer_id' => 1001,
            'bins' => [[
                'bin_id' => 999990,
                'quantity' => 100
            ]]
        ];

        $this->partialMock(PartRepository::class, function ($mock) use ($testPart, $testBinQty) {
            /** @var Mock $mock */
            $mock->shouldReceive('createBinQuantity')
                ->once()
                ->andReturns($testBinQty);
            $mock->shouldReceive('createPart')
                ->once()
                ->andReturns($testPart);
        });

        Event::fake();

        /** @var PartRepository $repo */
        $repo = app(PartRepository::class);
        $repo->create($testData);

        Event::assertDispatched(PartQtyUpdated::class, function ($event) use ($testPart) {
            /** @var PartQtyUpdated $event */
            return $event->part === $testPart;
        });
        Event::assertDispatched(PartQtyUpdated::class, function ($event) use ($testBinQty, $testPart) {
            /** @var PartQtyUpdated $event */
            return $event->binQuantity === $testBinQty;
        });
        Event::assertDispatched(PartQtyUpdated::class, function ($event) use ($testBinQty, $testPart) {
            /** @var PartQtyUpdated $event */
            return $event->details['quantity'] === 100;
        });
        Event::assertDispatched(PartQtyUpdated::class, function ($event) use ($testBinQty, $testPart) {
            /** @var PartQtyUpdated $event */
            return $event->details['description'] === 'Part created via bulk uploader';
        });
    }

    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testUpdateWillFireAuditLogEvent()
    {
        $testPart = new Part();
        $testPart->id = 123451;

        $testBinQty = new BinQuantity();
        $testBinQty->bin_id = 999990;
        $testBinQty->part_id = 123451;
        $testBinQty->qty = 100;

        $testData = [
            'id' => 123451,
            'dealer_id' => 1001,
            'bins' => [[
                'bin_id' => 999990,
                'quantity' => 100
            ]]
        ];

        $this->partialMock(PartRepository::class, function ($mock) use ($testPart, $testBinQty) {
            /** @var Mock $mock */
            $mock->shouldReceive('createBinQuantity')
                ->once()
                ->andReturns($testBinQty);
            $mock->shouldReceive('get')
                ->once()
                ->andReturns(
                    $this->mock(Part::class, function ($mock) use ($testPart) {
                        /** @var Mock $mock */
                        $mock->shouldReceive('getAttribute')
                            ->twice()
                            ->andReturns(12345);
                        $mock->shouldReceive('setAttribute')->once();
                        $mock->shouldReceive('fill')->once();
                        $mock->shouldReceive('bins')->once()->andReturns(
                            $this->mock(Bin::class, function($mock) {
                                $mock->shouldReceive('delete');
                            })
                        );
                        $mock->shouldReceive('save')->once()->andReturns(true);
                    })
                );
        });


        Event::fake();

        /** @var PartRepository $repo */
        $repo = app(PartRepository::class);
        $repo->update($testData);

        Event::assertDispatched(PartQtyUpdated::class, function ($event) use ($testPart) {
            /** @var PartQtyUpdated $event */
            return $event->part->id === 12345;
        });
        Event::assertDispatched(PartQtyUpdated::class, function ($event) use ($testBinQty, $testPart) {
            /** @var PartQtyUpdated $event */
            return $event->binQuantity === $testBinQty;
        });
        Event::assertDispatched(PartQtyUpdated::class, function ($event) use ($testBinQty, $testPart) {
            /** @var PartQtyUpdated $event */
            return $event->details['quantity'] === 100;
        });
        Event::assertDispatched(PartQtyUpdated::class, function ($event) use ($testBinQty, $testPart) {
            /** @var PartQtyUpdated $event */
            return $event->details['description'] === 'Part updated via bulk uploader';
        });
    }
}
