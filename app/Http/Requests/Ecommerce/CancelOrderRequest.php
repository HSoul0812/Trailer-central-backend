<?php

declare(strict_types=1);

namespace App\Http\Requests\Ecommerce;

use App\Http\Requests\Request;

/**
 * @property int $textrail_order_id
 */
class CancelOrderRequest extends Request
{
    public function getRules(): array
    {
        return [
            'textrail_order_id' => 'integer|min:1|required|exists:ecommerce_completed_orders,ecommerce_order_id'
        ];
    }
}
