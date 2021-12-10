<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Refund;

use App\Exceptions\Ecommerce\RefundPaymentGatewayException;
use App\Models\Ecommerce\Refund;

interface RefundServiceInterface
{
    /**
     * It will create a partial refund in our database, then it will send a return request to TexTrail,
     * but the partial refund process on the payment gateway will be remaining as pending until TextTrail send us a command to proceed.
     *
     * @param RefundBag $refundBag
     * @return Refund
     */
    public function issueReturn(RefundBag $refundBag): Refund;

    /**
     * It will create a full refund in our database, then it should enqueue a full refund process on the payment gateway
     *
     * @param RefundBag $refundBag
     * @return Refund
     */
    public function cancelOrder(RefundBag $refundBag): Refund;

    /**
     * It will call the refund process on the payment gateway and will create a refund on Magento side
     *
     * @param int $refundId
     * @throws RefundPaymentGatewayException when there were some error trying to refund the payment on the payment processor
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function refund(int $refundId): void;

    /**
     * It will mark the return as approved or denied, then if the return is approved it will enqueue
     * a job to process the refund on the payment processor.
     *
     * @param Refund $refund
     * @param array<array{sku: string, qty: int}> $parts array of parts indexed by part sku
     * @return bool
     */
    public function updateReturnStatus(Refund $refund, array $parts): bool;

    /**
     * It will create a refund on Textrail side,then it will be marked as completed
     *
     * @param int $refundId
     * @return bool
     */
    public function notify(int $refundId): bool;
}
