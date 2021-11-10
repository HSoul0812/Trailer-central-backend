<?php

declare(strict_types=1);

namespace App\Listeners\Ecommerce;

use App\Events\Ecommerce\OrderSuccessfullyPaid;
use App\Jobs\Ecommerce\SyncOrderJob;
use Illuminate\Foundation\Bus\DispatchesJobs;

class SendOrderToTextrail
{
    use DispatchesJobs;

    public function handle(OrderSuccessfullyPaid $event): void
    {
        $job = new SyncOrderJob($event->order->id);
        $this->dispatch($job->onQueue(config('ecommerce.textrail.queue')));
    }
}
