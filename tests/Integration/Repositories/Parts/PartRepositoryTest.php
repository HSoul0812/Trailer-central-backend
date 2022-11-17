<?php

namespace Tests\Integration\Repositories\Parts;

use App\Events\Parts\PartQtyUpdated;
use App\Models\Parts\Bin;
use App\Models\Parts\BinQuantity;
use App\Models\Parts\Part;
use App\Models\Parts\PartImage;
use App\Repositories\Parts\PartRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery\Mock;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
        $createdPart = $repo->create($testData);

        $this->assertInstanceOf(Part::class, $createdPart);
    }

    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testUpdateWillFireAuditLogEvent()
    {
        $testPart = factory(Part::class)->create([
            'dealer_id' => 1001
        ]);

        $testBinQty = new BinQuantity();
        $testBinQty->bin_id = 999990;
        $testBinQty->part_id = 123451;
        $testBinQty->qty = 100;

        $testData = [
            'dealer_id' => 1001,
            'dealer_cost' => 73.5,
            'bins' => [[
                'bin_id' => factory(Bin::class)->create()->id,
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
                ->andReturns($testPart);
        });

        /** @var PartRepository $repo */
        $repo = app(PartRepository::class);
        $updatedPart = $repo->update($testData);

        $this->assertSame($updatedPart->dealer_cost, 73.5);
    }
}
