<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Payment\Gateways;

use App\Exceptions\Ecommerce\RefundPaymentGatewayException;
use Brick\Money\Money;

interface PaymentGatewayServiceInterface
{
    /**
     * @param  string  $objectId
     * @param  Money  $amount
     * @param  string|null  $reason
     * @param  array<array{sku:string, title:string, id:int, amount: float}> $parts
     *
     * @return PaymentGatewayRefundResultInterface
     * @noinspection ReturnTypeCanBeDeclaredInspection
     * @noinspection PhpMissingReturnTypeInspection
     *
     * @throws RefundPaymentGatewayException when there was some error on payment gateway remote process
     */
    public function refund(string $objectId, Money $amount, array $parts = [], ?string $reason = null);
}
