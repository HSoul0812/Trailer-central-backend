<?php

declare(strict_types=1);

namespace App\Http\Requests\Ecommerce;

use App\Http\Requests\Request;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;

/**
 * @property int $dealer_id
 * @property int $order_id
 */
class GetSingleCompletedOrderRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'order_id' => 'integer|min:1|required|exists:ecommerce_completed_orders,id'
    ];

    protected function getObject(): CompletedOrder
    {
        return new CompletedOrder();
    }

    protected function validateObjectBelongsToUser(): bool
    {
        // only will check this rule when `order_id` was provided
        return (bool)$this->order_id;
    }

    protected function getObjectIdValue(): ?int
    {
        return $this->order_id;
    }
}
