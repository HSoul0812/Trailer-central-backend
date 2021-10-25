<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Payment\Gateways\Stripe;

use App\Services\Ecommerce\Payment\RefundResultInterface;

interface StripeRefundResultInterface extends RefundResultInterface
{
    public function getOriginalStatus(): string;

    public function getBalanceTransaction(): string;

    public function getCharge(): string;
}
