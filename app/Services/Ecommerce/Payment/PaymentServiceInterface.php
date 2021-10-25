<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Payment;

use App\Models\Ecommerce\Refund;
use Brick\Money\Money;

interface PaymentServiceInterface
{
    /**
     * @param  int  $id
     * @param  Money  $amount
     * @param  array<int> $parts part's ids to be refunded
     * @param  string|null  $reason
     * @return Refund
     */
    public function refund(int $id, Money $amount, array $parts, ?string $reason = null): Refund;
}
