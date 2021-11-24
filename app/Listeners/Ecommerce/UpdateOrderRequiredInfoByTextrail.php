<?php

declare(strict_types=1);

namespace App\Listeners\Ecommerce;

use App\Events\Ecommerce\OrderSuccessfullySynced;
use App\Jobs\Ecommerce\UpdateOrderRequiredInfoByTextrailJob;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Updates order items ids using textrail order item ids, and updates order log code as well.
 * Relevant process to be able refunding orders.
 */
class UpdateOrderRequiredInfoByTextrail
{
    use DispatchesJobs;

    public function handle(OrderSuccessfullySynced $event): void
    {
        $job = new UpdateOrderRequiredInfoByTextrailJob($event->order->id);
        $this->dispatch($job->onQueue(config('ecommerce.textrail.queue')));
    }
}
