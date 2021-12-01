<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Refund;

use App\Exceptions\Ecommerce\RefundPaymentGatewayException;
use App\Models\Ecommerce\Refund;

interface RefundServiceInterface
{
    /**
     * It will create a full/partial refund in our database, then it will send a return request to TexTrail,
     * but the refund process on the payment gateway will be remaining as pending until TextTrail send us a command to proceed.
     *
     * @param RefundBag $refundBag
     * @return Refund
     */
    public function issue(RefundBag $refundBag): Refund;

    /**
     * It will create a full refund in the database, then it should enqueue a refund process on the payment gateway
     * when it reaches the `return_receive` status.
     *
     * @param RefundBag $refundBag
     * @return Refund
     */
    public function cancelOrder(RefundBag $refundBag): Refund;

    /**
     * It will call the refund process on the payment gateway.
     *
     * @param int $refundId
     * @throws RefundPaymentGatewayException
     */
    public function refund(int $refundId): void;

    /**
     * @param Refund $refund
     * @param string $status
     * @param array<array{sku: string, qty: int}> $parts array of parts indexed by part sku
     * @return bool
     */
    public function updateStatus(Refund $refund, string $status, array $parts): bool;
}
