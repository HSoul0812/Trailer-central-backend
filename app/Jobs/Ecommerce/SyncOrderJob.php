<?php

declare(strict_types=1);

namespace App\Jobs\Ecommerce;

use App\Jobs\Job;
use App\Services\Ecommerce\CompletedOrder\CompletedOrderServiceInterface;

class SyncOrderJob extends Job
{
    /** @var int */
    private $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @param CompletedOrderServiceInterface $service
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Exceptions\Ecommerce\TextrailSyncException when some thing goes wrong on Magento side
     */
    public function handle(CompletedOrderServiceInterface $service): void
    {
        $service->syncSingleOrderOnTextrail($this->orderId);
    }
}
