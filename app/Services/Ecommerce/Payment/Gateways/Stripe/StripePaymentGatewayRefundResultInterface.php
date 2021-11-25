<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Payment\Gateways\Stripe;

use App\Services\Ecommerce\Payment\Gateways\PaymentGatewayRefundResultInterface;

interface StripePaymentGatewayRefundResultInterface extends PaymentGatewayRefundResultInterface
{
    public function getOriginalStatus(): string;

    public function getBalanceTransaction(): string;

    public function getCharge(): string;
}
