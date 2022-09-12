<?php

namespace Tests\Unit\Listeners\Parts;

use App\Events\Parts\PartQtyUpdated;
use App\Listeners\Parts\PartQtyAuditLogNotification;
use App\Models\Parts\BinQuantity;
use App\Models\Parts\Part;
use App\Repositories\Parts\AuditLogRepositoryInterface;
use Mockery\Mock;
use Tests\TestCase;

class PartQtyAuditLogNotificationTest extends TestCase
{
    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testNotificationCallsRepositoryCorrectly()
    {
        // data
        $part = new Part();
        $part->id = 12345;

        $binQty = new BinQuantity();
        $binQty->bin_id = 2;
        $binQty->qty = 10;

        $details = [
            'quantity' => 5,
            'description' => 'Test description 1',
        ];

        $event = new PartQtyUpdated($part, $binQty, $details);

        // mock
        $this->mock(AuditLogRepositoryInterface::class, function($mock) {
            /** @var Mock $mock */
            $mock->shouldReceive('create')
                ->once()
                ->with([
                    'part_id' => 12345,
                    'bin_id' => 2,
                    'qty' => 5,
                    'balance' => 10,
                    'description' => 'Test description 1',
                ]);
        });

        // test
        /** @var PartQtyAuditLogNotification $notification */
        $notification = app(PartQtyAuditLogNotification::class);
        $notification->handle($event);
    }

    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testNotificationPartOnlyCallsRepositoryCorrectly()
    {
        // test data
        $part = new Part();
        $part->id = 12345;

        $binQty1 = new BinQuantity();
        $binQty1->bin_id = 2;
        $binQty1->qty = 10;
        $binQty2 = new BinQuantity();
        $binQty2->bin_id = 3;
        $binQty2->qty = 20;

        $part->setRelation('bins', [$binQty1, $binQty2]);

        $details = [
            'description' => 'Test part only 1',
        ];

        // mock
        $this->mock(AuditLogRepositoryInterface::class, function($mock) {
            /** @var Mock $mock */
            $mock->shouldReceive('create')
                ->once()
                ->with([
                    'part_id' => 12345,
                    'bin_id' => 2,
                    'qty' => 0,
                    'balance' => 10,
                    'description' => 'Test part only 1',
                ]);
            $mock->shouldReceive('create')
                ->once()
                ->with([
                    'part_id' => 12345,
                    'bin_id' => 3,
                    'qty' => 0,
                    'balance' => 20,
                    'description' => 'Test part only 1',
                ]);
        });

        $event = new PartQtyUpdated($part, null, $details);

        /** @var PartQtyAuditLogNotification $notification */
        $notification = app(PartQtyAuditLogNotification::class);
        $notification->handle($event);
    }

}
