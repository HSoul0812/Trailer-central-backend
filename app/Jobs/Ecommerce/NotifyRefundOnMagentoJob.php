<?php

declare(strict_types=1);

namespace App\Jobs\Ecommerce;

use App\Exceptions\Ecommerce\RefundFailureException;
use App\Jobs\Job;
use App\Services\Ecommerce\Refund\RefundServiceInterface;

class NotifyRefundOnMagentoJob extends Job
{
    /** @var int */
    private $refundId;

    public function __construct(int $refundId)
    {
        $this->refundId = $refundId;
    }

    /**
     * @param RefundServiceInterface $service
     *
     * @throws RefundFailureException when it was not possible to create the refund on Textrail side
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function handle(RefundServiceInterface $service): void
    {
        $service->notify($this->refundId);
    }
}
