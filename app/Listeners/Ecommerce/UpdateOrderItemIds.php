<?php

declare(strict_types=1);

namespace App\Listeners\Ecommerce;

use App\Events\Ecommerce\OrderSuccessfullySynced;
use App\Jobs\Ecommerce\UpdateOrderItemsJob;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Updates order items ids using textrail order item ids.
 * Relevant process to be able refunding orders.
 */
class UpdateOrderItemIds
{
    use DispatchesJobs;

    public function handle(OrderSuccessfullySynced $event): void
    {
        $job = new UpdateOrderItemsJob($event->order->id);
        $this->dispatch($job->onQueue(config('ecommerce.textrail.queue')));
    }
}
