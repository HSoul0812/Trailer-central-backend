<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Payment;

use App\Models\Ecommerce\Refund;
use Brick\Money\Money;

interface PaymentServiceInterface
{
    /**
     * @param  int  $orderId
     * @param  Money  $amount
     * @param  array{id:int, amount: float} $parts part's ids to be refunded
     * @param  string|null  $reason
     * @return Refund
     */
    public function refund(int $orderId, Money $amount, array $parts, ?string $reason = null): Refund;
}
