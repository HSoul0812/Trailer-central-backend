<?php

declare(strict_types=1);

namespace App\Listeners\Ecommerce;

use App\Events\Ecommerce\OrderSuccessfullyPaid;
use App\Events\Ecommerce\QtyUpdated;

class UpdateOrderPartsQty
{
    public function handle(OrderSuccessfullyPaid $event): void
    {
        // Dispatch for handle quantity reducing.
        foreach ($event->order->parts as $part) {
            QtyUpdated::dispatch($part['id'], $part['qty']);
        }
    }
}
