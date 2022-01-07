<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Payment\Gateways;

use App\Exceptions\Ecommerce\RefundPaymentGatewayException;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use Brick\Money\Money;
use Stripe\PaymentIntent;

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

    public function getInvoice(CompletedOrder $completedOrder): array;

    public function updatePaymentIntent(array $params): bool;

    public function confirmPaymentIntent(array $params): bool;

    public function retrievePaymentIntent(array $params): PaymentIntent;

    public function paymentIntentSucceeded(array $params): bool;
}
