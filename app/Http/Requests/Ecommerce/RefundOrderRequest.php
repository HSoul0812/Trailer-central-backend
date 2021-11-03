<?php

declare(strict_types=1);

namespace App\Http\Requests\Ecommerce;

use App\Http\Requests\Request;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Ecommerce\Refund;
use Brick\Money\Money;

/**
 * @property int $dealer_id
 * @property int $order_id
 * @property float $amount
 * @property array{id: int, amount: float} $parts
 * @property string $reason
 */
class RefundOrderRequest extends Request
{
    public function getRules(): array
    {
        return [
            'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
            'order_id' => 'integer|min:1|required|exists:ecommerce_completed_orders,id',
            'amount' => 'required|min:1',
            'parts' => 'array',
            'parts.*.id' => 'required|integer|min:1',
            'parts.*.amount' => 'required|numeric',
            'reason' => sprintf('nullable|string|in:%s', implode(',', Refund::REASONS))
        ];
    }

    public function orderId(): int
    {
        return (int)$this->input('order_id');
    }

    public function amount(): Money
    {
        return Money::of($this->input('amount', 0), 'USD');
    }

    /**
     * @return array array indexed by part id
     */
    public function parts(): array
    {
        $indexedParts = [];

        foreach ($this->input('parts', []) as $part) {
            $indexedParts[$part['id']] = $part;
        }

        return $indexedParts;
    }

    public function reason(): ?string
    {
        return $this->input('reason');
    }

    protected function getObject(): CompletedOrder
    {
        return new CompletedOrder();
    }

    protected function validateObjectBelongsToUser(): bool
    {
        // only will check this rule when `order_id` was provided
        return (bool)$this->orderId();
    }

    protected function getObjectIdValue(): int
    {
        return $this->orderId();
    }
}
