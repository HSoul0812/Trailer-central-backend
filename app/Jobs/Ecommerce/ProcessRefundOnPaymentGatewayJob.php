<?php

declare(strict_types=1);

namespace App\Jobs\Ecommerce;

use App\Jobs\Job;
use App\Services\Ecommerce\Refund\RefundServiceInterface;

class ProcessRefundOnPaymentGatewayJob extends Job
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
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function handle(RefundServiceInterface $service): void
    {
        $service->refund($this->refundId);
    }
}
