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
                    'partId' => 12345,
                    'binId' => 2,
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
}
