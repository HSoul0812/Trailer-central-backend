<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Refund;

use App\Exceptions\Ecommerce\RefundPaymentGatewayException;
use App\Models\Ecommerce\Refund;
use App\Services\Ecommerce\Payment\Gateways\PaymentGatewayRefundResultInterface;

interface RefundServiceInterface
{
    /**
     * It will create a full/partial refund in our database, then it will send a refund/memo request to TexTrail,
     * but the refund process on the payment gateway will be remaining as pending until TextTrail send us a command to proceed.
     *
     * @param RefundBag $refundBag
     * @return Refund
     */
    public function issue(RefundBag $refundBag): Refund;

    /**
     * It will create a full refund in the database, then it will enqueue a refund process on the payment gateway.
     *
     * @param int $orderId
     * @return Refund
     */
    public function makeReturn(int $orderId): Refund;

    /**
     * It will call the refund process on the payment gateway.
     *
     * @param int $refundId
     * @return PaymentGatewayRefundResultInterface
     * @throws RefundPaymentGatewayException
     */
    public function refund(int $refundId): PaymentGatewayRefundResultInterface;
}
