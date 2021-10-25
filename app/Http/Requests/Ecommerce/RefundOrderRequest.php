<?php

declare(strict_types=1);

namespace App\Http\Requests\Ecommerce;

use App\Http\Requests\Request;
use App\Models\Ecommerce\Refund;
use Brick\Money\Money;

class RefundOrderRequest extends Request
{
    public function getRules(): array
    {
        return [
            'order_id' => 'integer|min:1|required|exists:ecommerce_completed_orders,id',
            'amount' => 'required|min:1',
            'parts' => 'nullable|array',
            'parts.*' => 'integer|min:1',
            'reason' => sprintf('nullable|string|in:%s', implode(',', Refund::REASONS))
        ];
    }

    public function orderId(): int
    {
        return (int) $this->input('order_id');
    }

    public function amount(): Money
    {
        return Money::of($this->input('amount', 0), 'USD');
    }

    public function parts(): array
    {
        return $this->input('parts', []);
    }

    public function reason(): ?string
    {
        return $this->input('reason');
    }
}
