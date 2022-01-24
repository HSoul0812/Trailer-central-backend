<?php

declare(strict_types=1);

namespace App\Http\Requests\Ecommerce\Refund;

use App\Http\Requests\Request;
use App\Models\Ecommerce\Refund;

/**
 * @property int $dealer_id
 * @property int $refund_id
 */
class GetSingleRefundRequest extends Request
{
    public function getRules(): array
    {
        return [
            'dealer_id' => 'required|integer|exists:dealer,dealer_id',
            'refund_id' => 'required|integer|exists:ecommerce_order_refunds,id',
        ];
    }

    protected function getObject(): Refund
    {
        return new Refund();
    }

    protected function validateObjectBelongsToUser(): bool
    {
        // only will check this rule when `refund_id` was provided
        return (bool)$this->refund_id;
    }

    protected function getObjectIdValue(): ?int
    {
        return $this->refund_id;
    }
}
