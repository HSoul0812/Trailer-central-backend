<?php

declare(strict_types=1);

namespace App\Http\Requests\Ecommerce\Refund;

use App\Http\Requests\Request;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Ecommerce\Refund;

/**
 * @property int $dealer_id
 * @property int $order_id
 * @property array{id: int, qty: int} $parts
 * @property string $reason
 */
class IssueReturnRequest extends Request
{
    public function getRules(): array
    {
        return [
            'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
            'order_id' => 'integer|min:1|required|exists:ecommerce_completed_orders,id',
            'parts' => 'array|required',
            'parts.*.id' => 'required|integer|min:1',
            'parts.*.qty' => 'required|int:min:1',
            'reason' => sprintf('nullable|string|in:%s', implode(',', Refund::REASONS))
        ];
    }

    public function orderId(): int
    {
        return (int)$this->input('order_id');
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
